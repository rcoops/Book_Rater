<?php

namespace Tests\BookRaterRaterBundle\Controller\Api;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Tests\BookRaterRaterBundle\ApiTestCase;

class BookControllerTest extends ApiTestCase
{

    /**
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
        $this->asserter()->assertResponseLocationHeaderEndswith($response, self::BASE_API_URI.'/books/1');
        $this->asserter()->assertResponseHeaderEquals($response, 'Content-Type', 'application/hal+json');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'The Importance of Adequate Toilet Facilities');
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn', '0123456789');
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn13', '9780123456789');
        $this->asserter()->assertResponsePropertyEquals($response, 'publisher', 'Cool Publishing Co.');
        $this->asserter()->assertResponsePropertyEquals($response, 'publishDate', '1984-08-24');
        $this->asserter()->assertResponsePropertyEquals($response, 'edition', 1);
        $this->asserter()->assertResponsePropertyIsArray($response, 'authors');
        $this->asserter()->assertResponsePropertyCount($response, 'authors', 1);
        $this->asserter()->assertResponsePropertyEquals($response, 'authors[0].lastName', 'Freeley');
    }

    /**
     * @throws \Exception
     */
    public function testGETBook()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);

        $response = $this->get('/books/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'title',
            'isbn',
            'isbn13',
            'edition',
            'publisher',
            'publishDate',
            'authors',
            'reviews',
            'averageRating',
        ]);
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'A Great Book');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri(self::BASE_API_URI . '/books/1')
        );
    }

    /**
     * @throws \Exception
     */
    public function testGETBookGroupSerialization()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
            'booksAuthored' => [
                $book,
            ]
        ]);
        $this->createReview([
            'title' => 'It was ok',
            'book' => $book,
        ]);

        $response = $this->get('/books/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'title',
            'isbn',
            'isbn13',
            'edition',
            'publisher',
            'publishDate',
            'authors',
            'reviews',
            'averageRating',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'authors[0].firstName');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'authors[0].booksAuthored');
        $this->asserter()->assertResponsePropertyExists($response, 'reviews[0].title');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'reviews[0].book');
    }

    /**
     * @throws \Exception
     */
    public function testGETBooksCollection()
    {
        $this->createBook([
            'title' => 'A Great Book',
            'isbn' => '0123456789',
            'isbn13' => '9780123456789',
        ]);
        $this->createBook([
            'title' => 'A Greater Book',
            'isbn' => '0123456780',
            'isbn13' => '9780123456780',
        ]);

        $response = $this->get('/books');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].title', 'A Greater Book');
    }

    /**
     * @throws \Exception
     */
    public function testGETBooksCollectionPaginated()
    {
        $author = $this->createAuthor([
            'firstName' => 'Tony',
            'lastName' => 'Stark',
        ]);
        $this->createBook([
            'title' => 'NotMatching',
            'isbn' => '0987654321',
            'isbn13' => '9780987654321',
            'authors' => [$author],
        ]);

        for ($i = 0; $i < 25; $i++) {
            $j = sprintf("%02d", $i); // as list ordered by last name and 10 comes before 2
            $this->createBook([
                'title' => 'Book' . $j,
                'isbn' => '01234567' . $j,
                'isbn13' => '97801234567' . $j,
                'authors' => [$author],
            ]);
        }

        // page 1
        $response = $this->get('/books?filter=book');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].title',
            'Book05'
        );

        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].title',
            'Book24'
        );
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].title');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);

        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, '_links.prev');
        // Use unaltered client from here on as the links we get are correct
        $response = $this->client->get($nextLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].title',
            'Book15'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
    }

    /**
     * @throws \Exception
     */
    public function testPUTBook()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $originalAuthor = $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
        ]);

        $replacementAuthor = $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
        ]);

        $this->createBook([
            'title' => 'A Great Book',
            'isbn' => '0123456789',
            'isbn13' => '9780123456789',
            'publisher' => 'Old Publishing Co.',
            'publishDate' => new \DateTime('23-12-2010'),
            'edition' => 1,
            'authors' => [$originalAuthor],
        ]);

        $data = [
            'title' => 'This Won\'t Happen',
            'isbn' => '0123123123',
            'isbn13' => '9780120120120',
            'publisher' => 'New Publishing Inc.',
            'publishDate' => '24-12-2010',
            'edition' => 2,
            'authorIds' => [$replacementAuthor->getId()],
        ];

        $response = $this->put('/books/1', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        // Unchanged due to restrictions (will be ignored by form)
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'A Great Book');
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn', '0123456789');
        // Changes allowed
        $this->asserter()->assertResponsePropertyEquals($response, 'isbn13', '9780120120120');
        $this->asserter()->assertResponsePropertyEquals($response, 'publisher', 'New Publishing Inc.');
        $this->asserter()->assertResponsePropertyEquals($response, 'publishDate', '2010-12-24');
        $this->asserter()->assertResponsePropertyEquals($response, 'edition', '2');
        $this->asserter()->assertResponsePropertyExists($response, 'authors');
        $this->asserter()->assertResponsePropertyIsArray($response, 'authors');
        $this->asserter()->assertResponsePropertyCount($response, 'authors', 1);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'authors[0].firstName',
            'Bruce'
        );
    }

    /**
     * @throws \Exception
     */
    public function testPATCHBook()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $originalAuthor = $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
        ]);

        $replacementAuthor = $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
        ]);

        $this->createBook([
            'title' => 'A Great Book',
            'authors' => [$originalAuthor],
        ]);

        $data = [
            'authorIds' => [$replacementAuthor->getId()],
        ];

        $response = $this->patch('/books/1', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        // Unchanged and not cleared due to PATCH
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'A Great Book');
        // Changed
        $this->asserter()->assertResponsePropertyExists($response, 'authors');
        $this->asserter()->assertResponsePropertyIsArray($response, 'authors');
        $this->asserter()->assertResponsePropertyCount($response, 'authors', 1);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'authors[0].lastName',
            'Wayne'
        );
    }

    /**
     * @throws \Exception
     */
    public function testDELETEBook()
    {
        $this->createUser('mr_admin', 'I know how to correctly make a password', true);
        $author = $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => ''
        ]);
        $this->createBook([
            'title' => 'A Great Book',
            'authors' => [$author],
        ]);

        $response = $this->delete('/books/1', [
            'headers' => $this->getAuthorizedHeaders('mr_admin'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());

        $this->getEntityManager()->clear(); // Clear the em to force real db querying

        $book = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Book')
            ->find(1);
        $this->assertNull($book);
        $author = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Author')
            ->find($author->getId());
        $this->assertNotNull($author);
    }

    /**
     * @throws \Exception
     */
    public function testFollowBookChildCollectionLinks()
    {
        $user = $this->createUser('mr_test', 'MostSecretestPassword');

        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $this->createAuthor([
            'firstName' => 'Clerk',
            'lastName' => 'Kent',
            'initial' => '',
            'booksAuthored' => [$book],
        ]);

        $this->createAuthor([
            'firstName' => 'Bruce',
            'lastName' => 'Banner',
            'initial' => '',
            'booksAuthored' => [$book],
        ]);

        $this->createReview([
            'title' => 'My Most Favouritest Book',
            'user' => $user,
            'book' => $book,
        ]);

        $response = $this->get('/books/1');
        $authorsUri = $this->asserter()->readResponseProperty($response, '_links.authors');
        $reviewsUri = $this->asserter()->readResponseProperty($response, '_links.reviews');

        $response = $this->client->get($authorsUri);
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $author = $this->asserter()->readResponseProperty($response, 'items[1]');
        $this->assertEquals('Bruce', $author->firstName);

        $response = $this->client->get($reviewsUri);
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $review = $this->asserter()->readResponseProperty($response, 'items[0]');
        $this->assertEquals('My Most Favouritest Book', $review->title);
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

        $response = $this->post('/books', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.isbn[0]', 'ISBN must be entered.');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.title');

        $data = [
            'title' => 'Just a Title',
            'isbn' => '0123456789',
            'isbn13' => '978012345678*'
        ];

        $response = $this->post('/books', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test')
        ]);

        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.isbn13[0]', 'ISBN 13 must 13 digits.');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.title');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.isbn');
    }

    /**
     * @throws \Exception
     */
    public function test404Exception()
    {
        $response = $this->get('/books/2');

        $this->assertEquals(404, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No book found with id: "2"');
    }

    public function testPOSTRequiresAuthentication()
    {
        $response = $this->post('/books', [
            'body' => '[]'
            // no auth
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPUTRequiresAuthentication()
    {
        $response = $this->put('/books/2', [
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

        $this->createBook([]);
        $response = $this->delete('/books/1');
        $this->assertEquals(401, $response->getStatusCode());
    }

}
