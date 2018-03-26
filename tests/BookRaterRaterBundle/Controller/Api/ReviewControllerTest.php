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

}
