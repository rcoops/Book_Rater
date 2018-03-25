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
//        $this->assertTrue($response->hasHeader('Location'));
//        $this->assertStringEndsWith(self::BASE_API_URI.'/reviews/1', $response->getHeader('Location')[0]);
        $this->assertEquals('application/hal+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyExists($response, 'title');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'title',
            'My Most Favouritest Book'
        );
        $this->asserter()->assertResponsePropertyExists($response, 'comments');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'comments',
            'This is my most favouritest book. I especially like the pictures.'
        );
        $this->asserter()->assertResponsePropertyExists($response, 'rating');
        $this->asserter()->assertResponsePropertyEquals($response, 'rating', 5);
        $this->asserter()->assertResponsePropertyExists($response, 'book');
        $this->asserter()->assertResponsePropertyExists($response, 'book.title');
        $this->asserter()->assertResponsePropertyEquals($response, 'book.title', 'A Great Book');
        $this->asserter()->assertResponsePropertyExists($response, 'user');
        $this->asserter()->assertResponsePropertyExists($response, 'user.username');
        $this->asserter()->assertResponsePropertyEquals($response, 'user.username', 'mr_test');
    }

}
