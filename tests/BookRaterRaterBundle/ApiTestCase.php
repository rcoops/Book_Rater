<?php

namespace Tests\BookRaterRaterBundle;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Throwable;
use TypeError;

class ApiTestCase extends KernelTestCase
{
    protected const BASE_API_URI = '/api/v1'; // single entry point for all api calls

    private static $staticClient;

    /**
     * @var array
     */
    private static $history = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    private $responseAsserter;

    public static function setUpBeforeClass()
    {
        $handler = HandlerStack::create();

        $handler->push(Middleware::history(self::$history));
        $handler->push(Middleware::mapRequest(function(RequestInterface $request) {
            $path = $request->getUri()->getPath();
            if (strpos($path, '/app_test.php') !== 0) {
                $path = '/app_test.php' . $path;
            }
            $uri = $request->getUri()->withPath($path);

            return $request->withUri($uri);
        }));

        $baseUrl = getenv('TEST_BASE_URL');
        if (!$baseUrl) {
            static::fail('No TEST_BASE_URL environmental variable set in phpunit.xml.');
        }
        self::$staticClient = new Client([
            'base_uri' => $baseUrl,
            'http_errors' => false,
            'handler' => $handler
        ]);

        self::bootKernel();
    }

    protected function setUp()
    {
        $this->client = self::$staticClient;
        // reset the history
        self::$history = [];

        $this->purgeDatabase();
    }

    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown()
    {
        // purposefully not calling parent class, which shuts down the kernel
    }

    /**
     * @param Throwable $e
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(Throwable $e)
    {
        if ($lastResponse = $this->getLastResponse()) {
            $this->printDebug('');
            $this->printDebug('<error>Failure!</error> when making the following request:');
            $this->printLastRequestUrl();
            $this->printDebug('');

            $this->debugResponse($lastResponse);
        }

        throw $e;
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    protected function printLastRequestUrl()
    {
        $lastRequest = $this->getLastRequest();

        if ($lastRequest) {
            $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUri()));
        } else {
            $this->printDebug('No request was made.');
        }
    }

    protected function debugResponse(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            $this->printDebug(sprintf('%s: %s', $name, implode(', ', $values)));
        }
        $body = (string) $response->getBody();

        $contentType = $response->getHeader('Content-Type');
        $contentType = $contentType[0];
        if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
            $data = json_decode($body);
            if ($data === null) {
                // invalid JSON!
                $this->printDebug($body);
            } else {
                // valid JSON, print it pretty
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            // the response is HTML - see if we should print all of it or some of it
            $isValidHtml = strpos($body, '</body>') !== false;

            if ($isValidHtml) {
                $this->printDebug('');
                $crawler = new Crawler($body);

                // very specific to Symfony's error page
                $isError = $crawler->filter('#traces-0')->count() > 0
                    || strpos($body, 'looks like something went wrong') !== false;
                if ($isError) {
                    $this->printDebug('There was an Error!!!!');
                    $this->printDebug('');
                } else {
                    $this->printDebug('HTML Summary (h1 and h2):');
                }

                // finds the h1 and h2 tags and prints them only
                foreach ($crawler->filter('h1, h2')->extract(['_text']) as $header) {
                    // avoid these meaningless headers
                    if (strpos($header, 'Stack Trace') !== false) {
                        continue;
                    }
                    if (strpos($header, 'Logs') !== false) {
                        continue;
                    }

                    // remove line breaks so the message looks nice
                    $header = str_replace("\n", ' ', trim($header));
                    // trim any excess whitespace "foo   bar" => "foo bar"
                    $header = preg_replace('/(\s)+/', ' ', $header);

                    if ($isError) {
                        $this->printErrorBlock($header);
                    } else {
                        $this->printDebug($header);
                    }
                }

                /*
                 * When using the test environment, the profiler is not active
                 * for performance. To help debug, turn it on temporarily in
                 * the config_test.yml file (framework.profiler.collect)
                 */
                $profilerUrl = $response->getHeader('X-Debug-Token-Link');
                if ($profilerUrl) {
                    $fullProfilerUrl = $response->getHeader('Host')[0].$profilerUrl[0];
                    $this->printDebug('');
                    $this->printDebug(sprintf(
                        'Profiler URL: <comment>%s</comment>',
                        $fullProfilerUrl
                    ));
                }

                // an extra line for spacing
                $this->printDebug('');
            } else {
                $this->printDebug($body);
            }
        }
    }

    /**
     * Print a message out - useful for debugging
     *
     * @param $string
     */
    protected function printDebug($string)
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        $this->output->writeln($string);
    }

    /**
     * Print a debugging message out in a big red block
     *
     * @param $string
     */
    protected function printErrorBlock($string)
    {
        if ($this->formatterHelper === null) {
            $this->formatterHelper = new FormatterHelper();
        }
        $output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);

        $this->printDebug($output);
    }

    /**
     * @return RequestInterface
     */
    private function getLastRequest()
    {
        if (!self::$history || empty(self::$history)) {
            return null;
        }

        $history = self::$history;

        $last = array_pop($history);

        return $last['request'];
    }

    /**
     * @return ResponseInterface
     */
    private function getLastResponse()
    {
        if (!self::$history || empty(self::$history)) {
            return null;
        }

        $history = self::$history;

        $last = array_pop($history);

        return $last['response'];
    }

    /**
     * @param $username
     * @param string $plainPassword
     * @param bool $isAdmin
     * @return User
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createUser($username, $plainPassword = 'foo', $isAdmin = false)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@foo.com');
        $password = $this->getService('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $user->setEnabled(true);

        if ($isAdmin) {
            $user->addRole('ROLE_ADMIN');
        }

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }


    /**
     * @param array $data
     * @return Book
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TypeError
     */
    protected function createBook(array $data)
    {
        // Set defaults if not given
        $data = array_merge([
            'title' => 'Best Book Ever',
            'isbn' => '0123456789',
            'isbn13' => '012-0123456789',
            'edition' => 1,
            'publisher' => 'Awesome Books Publishing Inc.',
            'publishDate' => new \DateTime('now'),
        ], $data);

        $accessor = PropertyAccess::createPropertyAccessor();
        $book = new Book();
        foreach ($data as $key => $value) {
            $accessor->setValue($book, $key, $value);
        }

        $em = $this->getEntityManager();
        $em->persist($book);
        $em->flush();

        return $book;
    }

    /**
     * @param array $data
     * @return Author
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TypeError
     */
    protected function createAuthor(array $data)
    {
        $data = array_merge([
            'firstName' => 'Testy',
            'lastName' => 'Testface',
            'initial' => 'M C',
        ], $data);

        $accessor = PropertyAccess::createPropertyAccessor();
        $author = new Author();
        foreach ($data as $key => $value) {
            $accessor->setValue($author, $key, $value);
        }

        $em = $this->getEntityManager();
        $em->persist($author);
        $em->flush();

        return $author;

    }

    /**
     * @return ResponseAsserter
     */
    protected function asserter()
    {
        if ($this->responseAsserter === null) {
            $this->responseAsserter = new ResponseAsserter();
        }

        return $this->responseAsserter;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    /**
     * Call this when you want to compare URLs in a test
     *
     * (since the returned URL's will have /app_test.php in front)
     *
     * @param string $uri
     * @return string
     */
    protected function adjustUri($uri)
    {
        return '/app_test.php'.$uri;
    }

    protected function getAuthorizedHeaders($username, $headers = array())
    {
        $token = $this->getService('lexik_jwt_authentication.encoder')
            ->encode(['username' => $username]);

        $headers['Authorization'] = 'Bearer '.$token;

        return $headers;
    }

    /**
     * Convenience method for prepending the base api uri to the path
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    protected function post(string $uri, array $options = [])
    {
        return $this->client->post(self::BASE_API_URI.$uri, $options);
    }

    /**
     * Convenience method for prepending the base api uri to the path
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    protected function get(string $uri, array $options = [])
    {
        return $this->client->get(self::BASE_API_URI.$uri, $options);
    }

    /**
     * Convenience method for prepending the base api uri to the path
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    protected function put(string $uri, array $options = [])
    {
        return $this->client->put(self::BASE_API_URI.$uri, $options);
    }

    /**
     * Convenience method for prepending the base api uri to the path
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    protected function patch(string $uri, array $options = [])
    {
        return $this->client->patch(self::BASE_API_URI.$uri, $options);
    }

    /**
     * Convenience method for prepending the base api uri to the path
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    protected function delete(string $uri, array $options = [])
    {
        return $this->client->delete(self::BASE_API_URI.$uri, $options);
    }

}
