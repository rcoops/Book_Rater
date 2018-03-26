<?php

namespace Tests\BookRaterRaterBundle\Controller\Api;

use Tests\BookRaterRaterBundle\ApiTestCase;

class ReviewControllerTest extends ApiTestCase
{

    /**
     * @throws \Exception
     */
    public function testPOSTReview()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $data = [
            'title' => 'My Most Favouritest Book',
            'comments' => 'This is my most favouritest book. I especially like the pictures.',
            'rating' => 5,
            'bookId' => $book->getId(),
        ];

        $response = $this->post('/reviews', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->asserter()->assertResponseLocationHeaderEndswith($response, self::BASE_API_URI.'/reviews/1');
        $this->asserter()->assertResponseHeaderEquals($response, 'Content-Type', 'application/hal+json');
        $this->asserter()->assertResponsePropertiesExist($response, [
            'title',
            'comments',
            'rating',
            'book',
            'book.title',
            'user',
            'user.username',
        ]);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'title',
            'My Most Favouritest Book'
        );
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'comments',
            'This is my most favouritest book. I especially like the pictures.'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'rating', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'book.title', 'A Great Book');
        $this->asserter()->assertResponsePropertyEquals($response, 'user.username', 'mr_test');
    }

    /**
     * @throws \Exception
     */
    public function testGETReview()
    {
        $user = $this->createUser('mr_test', 'MostSecretestPassword');

        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $this->createReview([
            'title' => 'My Most Favouritest Book',
            'comments' => 'This is my most favouritest book. I especially like the pictures.',
            'rating' => 5,
            'book' => $book,
            'user' => $user,
        ]);

        $response = $this->get('/reviews/1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponseLocationHeaderEndswith($response, self::BASE_API_URI.'/reviews/1');
        $this->asserter()->assertResponsePropertiesExist($response, [
            'title',
            'comments',
            'rating',
            'book',
            'book.title',
            'user',
            'user.username',
        ]);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'title',
            'My Most Favouritest Book'
        );
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'comments',
            'This is my most favouritest book. I especially like the pictures.'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'rating', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'book.title', 'A Great Book');
        $this->asserter()->assertResponsePropertyEquals($response, 'user.username', 'mr_test');
    }

    /**
     * @throws \Exception
     */
    public function testGETReviewsCollection()
    {
        $user = $this->createUser('hated_the_book');
        $otherUser = $this->createUser('liked_the_book');
        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);
        $this->createReview([
            'title' => 'Didn\'t think it was so great',
            'rating' => 2,
            'user' => $user,
            'book' => $book,
        ]);
        $this->createReview([
            'title' => 'The clue is in the name',
            'rating' => 5,
            'user' => $otherUser,
            'book' => $book,
        ]);

        $response = $this->get('/reviews');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].rating', '5');
    }

    /**
     * @throws \Exception
     */
    public function testGETReviewsCollectionPaginated()
    {
        $book = $this->createBook([
            'title' => 'A Great Book',
            'isbn' => '0123456789',
            'isbn13' => '9780123456789',
        ]);
        $anotherBook = $this->createBook([
            'title' => 'Another Book',
            'isbn' => '0123456780',
            'isbn13' => '9780123456780',
        ]);
        $this->createReview([
            'title' => 'NotMatching',
            'rating' => 2,
            'user' => $this->createUser('NotMatching'),
            'book' => $anotherBook,
        ]);

        for ($i = 0; $i < 25; $i++) {
            $j = sprintf("%02d", $i); // as list ordered by last name and 10 comes before 2
            $this->createReview([
                'title' => 'Review'.$j,
                'rating' => 3,
                'user' => $this->createUser('User'.$j),
                'book' => $book,
            ]);
        }

        // page 1
        $response = $this->get('/reviews?filter=a%20great%20book');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].title',
            'Review05'
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
            'items[5].title',
            'Review15'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].title',
            'Review24'
        );

        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].title');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
    }

    /**
     * @throws \Exception
     */
    public function testPUTReview()
    {
        $user = $this->createUser('mr_test');

        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $this->createReview([
            'title' => 'My Most Favouritest Book',
            'comments' => 'This is my most favouritest book. I especially like the pictures.',
            'rating' => 5,
            'book' => $book,
            'user' => $user,
        ]);

        $data = [
            'title' => 'My Favourite Book',
            'comments' => 'This is my favourite book. The illustrations lend eloquence to the overall theme.',
            'rating' => 4,
        ];

        $response = $this->put('/reviews/1', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'My Favourite Book');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'comments',
            'This is my favourite book. The illustrations lend eloquence to the overall theme.');
        $this->asserter()->assertResponsePropertyEquals($response, 'rating', '4');
    }

    /**
     * @throws \Exception
     */
    public function testPATCHBook()
    {
        $user = $this->createUser('mr_test');

        $book = $this->createBook([
            'title' => 'A Great Book',
        ]);

        $this->createReview([
            'title' => 'My Most Favouritest Book',
            'comments' => 'This is my most favouritest book. I especially like the pictures.',
            'rating' => 5,
            'book' => $book,
            'user' => $user,
        ]);

        $data = [
            'rating' => 4,
        ];

        $response = $this->patch('/reviews/1', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        // Unchanged and not cleared due to PATCH
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'title',
            'My Most Favouritest Book'
        );
        // Changed
        $this->asserter()->assertResponsePropertyEquals($response, 'rating', '4');
    }

    /**
     * @throws \Exception
     */
    public function testDELETEReview()
    {
        $user = $this->createUser(
            'mr_test',
            'I know how to correctly make a password'
        );

        $book = $this->createBook([]);

        $this->createReview([
            'book' => $book,
            'user' => $user,
        ]);

        $response = $this->delete('/reviews/1', [
            'headers' => $this->getAuthorizedHeaders('mr_test'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());

        $this->getEntityManager()->clear(); // Clear the em to force real db querying

        $review = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Review')
            ->find(1);
        $this->assertNull($review);
        $book = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Book')
            ->find(1);
        $this->assertNotNull($book);
    }

    public function testPUTRequiresOwnerOrAdmin()
    {

    }

    public function testDELETERequiresOwnerOrAdmin()
    {

    }

}
