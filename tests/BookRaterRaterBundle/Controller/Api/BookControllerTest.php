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
        $author = $this->createAuthor([
            'firstName' => 'Ian',
            'lastName' => 'Freeley',
            'initial' => 'P',
        ]);

        $data = [
            'title' => 'The Importance of Adequate Toilet Facilities',
            'isbn' => '0123456789',
            'isbn13' => '9780123456789',
            'publisher' => 'Cool Publishing Co.',
            'publishDate' => '24-08-1984',
            'edition' => 1,
            'authorIds' => [
                $author->getId(),
            ]
        ];
        $response = $this->post('/books', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        $this->assertEquals(201, $response->getStatusCode());
//        $this->assertTrue($response->hasHeader('Location')); TODO
//        $this->assertStringEndsWith(self::BASE_API_URI.'/books/', $response->getHeader('Location')[0]);
        $this->assertEquals('application/hal+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'The Importance of Adequate Toilet Facilities');
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn', '0123456789');
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn13', '9780123456789');
        $this->asserter()->assertResponsePropertyEquals($response, 'publisher', 'Cool Publishing Co.');
        $this->assertStringStartsWith(
            '1984-08-24',
            $this->asserter()->readResponseProperty($response, 'publishDate')
        ); // TODO reformat date on exit...
        $this->asserter()->assertResponsePropertyEquals($response, 'edition', 1);
        $this->asserter()->assertResponsePropertyIsArray($response, 'authors');
        $this->asserter()->assertResponsePropertyCount($response, 'authors', 1);
        $this->asserter()->assertResponsePropertyEquals($response, 'authors[0].lastName', 'Freeley');
    }

}