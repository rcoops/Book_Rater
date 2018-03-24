<?php
/**
 * Created by PhpStorm.
 * User: rick
 * Date: 24/03/18
 * Time: 17:33
 */

namespace Tests\BookRaterRaterBundle\Controller\Api;


use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Tests\BookRaterRaterBundle\ApiTestCase;

class BookControllerTest extends ApiTestCase
{

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \TypeError
     * @throws \Exception
     */
    public function testPOSTBook()
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

}