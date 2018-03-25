<?php

namespace Tests\BookRaterRaterBundle\Controller\Api;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Tests\BookRaterRaterBundle\ApiTestCase;

class AuthorControllerTest extends ApiTestCase
{

    /**
     * @throws \Exception
     */
    public function testPOSTAuthor()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $data = [
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'bookIds' => [
                $book->getId(),
            ]
        ];
        $response = $this->post('/authors', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals('application/hal+json', $response->getHeader('Content-Type')[0]);
        $this->assertStringEndsWith(self::BASE_API_URI.'/authors/Wayne/Bruce', $response->getHeader('Location')[0]);
        $this->asserter()->assertResponsePropertyExists($response, 'lastName');
        $this->asserter()->assertResponsePropertyEquals($response, 'lastName', 'Wayne');
        $this->asserter()->assertResponsePropertyExists($response, 'firstName');
        $this->asserter()->assertResponsePropertyEquals($response, 'firstName', 'Bruce');
        $this->asserter()->assertResponsePropertyExists($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyIsArray($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyCount($response, 'booksAuthored', 1);
        $this->asserter()->assertResponsePropertyEquals($response, 'booksAuthored[0].title', 'A Great Book');
    }

    /**
     * @throws \Exception
     */
    public function testGETAuthor()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $otherBook = $this->createBook([
            'title' => 'A Greater Book',
            'isbn' => '0123456780',
            'isbn13' => '012-0123456780',
        ]);
        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'booksAuthored' => [
                $book,
                $otherBook,
            ]
        ]);

        $response = $this->get('/authors/Wayne/Bruce');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'firstName',
            'lastName',
            'initial',
            'booksAuthored',
        ]);
        $this->asserter()->assertResponsePropertyEquals($response, 'firstName', 'Bruce');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri(self::BASE_API_URI.'/authors/Wayne/Bruce')
        );
    }

    /**
     * @throws \Exception
     */
    public function testGETAuthorsCollection()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);
        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);

        $response = $this->get('/authors');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].firstName', 'Bruce');
    }

    /**
     * @throws \Exception
     */
    public function testGETAuthorsCollectionPaginated()
    {
        $this->createAuthor([
            'firstName' => 'NotMatching',
            'lastName' => 'Doe',
            'initial' => '',
        ]);

        for ($i = 0; $i < 25; $i++) {
            $j = sprintf("%02d", $i); // as list ordered by last name and 10 comes before 2
            $this->createAuthor([
                'firstName' => 'John'.$j,
                'initial' => '',
            ]);
        }

        // page 1
        $response = $this->get('/authors?filter=john');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].firstName',
            'John05'
        );

        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        // Use unaltered client from here on as the links we get are correct
        $response = $this->client->get($nextLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].firstName',
            'John15'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].firstName',
            'John24'
        );

        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].firstName');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
    }

    /**
     * @throws \Exception
     */
    public function testPUTAuthor()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $otherBook = $this->createBook([
            'title' => 'This one wasn\'t so great',
            'isbn' => '0123456780',
            'isbn13' => '012-0123456780',
        ]);

        $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);

        $data = [
            'firstName' => 'Peter',
            'lastName' => 'Parker',
            'initial' => 'P',
            'bookIds' => [
                $otherBook->getId(),
            ]
        ];

        $response = $this->put('/authors/Kent/Clerk', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        // Unchanged due to restrictions (will be ignored by form)
        $this->asserter()->assertResponsePropertyEquals($response, 'lastName', 'Kent');
        $this->asserter()->assertResponsePropertyEquals($response, 'firstName', 'Clerk');
        // Changes allowed
        $this->asserter()->assertResponsePropertyEquals($response, 'initial', 'P');
        $this->asserter()->assertResponsePropertyExists($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyIsArray($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyCount($response, 'booksAuthored', 1);
        $this->asserter()->assertResponsePropertyEquals($response,
            'booksAuthored[0].title',
            'This one wasn\'t so great'
        );
    }

    /**
     * @throws \Exception
     */
    public function testPATCHAuthor()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $otherBook = $this->createBook([
            'title' => 'This one wasn\'t so great',
            'isbn' => '0123456780',
            'isbn13' => '012-0123456780',
        ]);

        $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => 'P',
            'booksAuthored' => [
                $book,
            ]
        ]);

        $data = [
            'bookIds' => [
                $otherBook->getId(),
            ]
        ];

        $response = $this->patch('/authors/Kent/Clerk', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        // Not changed or cleared due to patch
        $this->asserter()->assertResponsePropertyEquals($response, 'initial', 'P');
        // Changed
        $this->asserter()->assertResponsePropertyExists($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyIsArray($response, 'booksAuthored');
        $this->asserter()->assertResponsePropertyCount($response, 'booksAuthored', 1);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'booksAuthored[0].title',
            'This one wasn\'t so great'
        );
    }

    /**
     * @throws \Exception
     */
    public function testDELETEAuthor()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $otherBook = $this->createBook([
            'title' => 'A Greater Book',
            'isbn' => '0123456780',
            'isbn13' => '012-0123456780',
        ]);
        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'booksAuthored' => [
                $book,
                $otherBook,
            ]
        ]);
        $this->createUser('mr_admin', 'I know how to correctly make a password', true);

        $response = $this->delete('/authors/Wayne/Bruce', [
            'headers' => $this->getAuthorizedHeaders('mr_admin'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());

        $repo = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Book');
        $book = $repo->find($book->getId());
        $this->assertNotNull($book);
    }

    /**
     * @throws \Exception
     */
    public function testFollowAuthorBooksLink()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);

        $response = $this->get('/authors/Kent/Clerk');
        $uri = $this->asserter()->readResponseProperty($response, '_links.books');
        $response = $this->client->get($uri);
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $book = $this->asserter()->readResponseProperty($response,'items[0]');
        $this->assertEquals('A Great Book', $book->title);
    }

    /**
     * @throws \Exception
     */
    public function testValidationErrors()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $data = [
            'title' => 'Just a Title',
        ];

        $response = $this->post('/authors', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyExists($response, 'errors.lastName');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.lastName[0]', 'Last name must not be blank.');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.firstName');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);

        $data = [
            'firstName' => '012',
            'lastName' => 'Coolington',
        ];

        $response = $this->post('/authors', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test')
        ]);

        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyExists($response, 'errors.firstName');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.firstName[0]', 'First name must consist of letters only.');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.lastName');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidJson()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $invalidBody = <<<EOF
{
    "firstName": "Bruce",
    "lastName" : "Wayne
    "booksAuthored": [1,2]
}
EOF;

        $response = $this->post('/authors', [
            'body' => $invalidBody,
            'headers' => $this->getAuthorizedHeaders('mr_test')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains($response, 'type', 'invalid_body_format');
    }

    /**
     * @throws \Exception
     */
    public function test404Exception()
    {
        $response = $this->get('/authors/mcfakerson/fakey');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No author found with last name: "mcfakerson" & first name: "fakey"');
    }

    public function testPOSTRequiresAuthentication()
    {
        $response = $this->post('/authors', [
            'body' => '[]'
            // no auth
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPUTRequiresAuthentication()
    {
        $response = $this->put('/authors/doesnt/matter', [
            'body' => '[]'
            // no auth
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @throws \Exception
     */
    public function testDELETERequiresAdminAuthentication()
    {
        $this->createUser('mr_test', 'MostSecretestPassword'); // Not an admin

        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
        ]);
        $response = $this->delete('/authors/Wayne/Bruce');
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @throws \Exception
     */
    public function testBadToken()
    {
        $response = $this->post('/authors', [
            'body' => '[]',
            'headers' => [
                'Authorization' => 'Bearer WRONG'
            ]
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Invalid token');
        $this->debugResponse($response);
    }

}
