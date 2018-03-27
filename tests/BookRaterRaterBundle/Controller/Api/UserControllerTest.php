<?php

namespace Tests\BookRaterRaterBundle\Controller\Api;

use Tests\BookRaterRaterBundle\ApiTestCase;

class UserControllerTest extends ApiTestCase
{

    /**
     * @throws \Exception
     */
    public function testGETUser()
    {
        $this->createUser('mr_test');
        $this->createUser('admin', 'admin', true);

        $response = $this->get('/users/1', [
            'headers' => $this->getAuthorizedHeaders('admin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertiesExist($response, [
            'id',
            'username',
            'email',
            'enabled',
            'reviews',
            'messages',
            '_links',
        ]);
        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'mr_test');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri(self::BASE_API_URI . '/users/1')
        );
    }

    /**
     * @throws \Exception
     */
    public function testGETUsersCollection()
    {
        $this->createUser('mr_test');
        $this->createUser('admin', 'admin', true);

        $response = $this->get('/users');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].username', 'admin');
    }

    /**
     * @throws \Exception
     */
    public function testGETUsersCollectionPaginated()
    {
        for ($i = 0; $i < 25; $i++) {
            $this->createUser('TestUser'.$i);
        }
        $this->createUser('admin', 'admin', true);

        // page 1
        $response = $this->get('/users', [
            'headers' => $this->getAuthorizedHeaders('admin'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].username',
            'TestUser5'
        );

        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 26);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].username',
            'TestUser24'
        );
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[6].username');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 6);

        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, '_links.prev');
        // Use unaltered client from here on as the links we get are correct
        $response = $this->client->get($nextLink);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].username',
            'TestUser15'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
    }

    /**
     * @throws \Exception
     */
    public function testDELETEUser()
    {
        $user = $this->createUser('mr_test');
        $this->createUser('admin', 'admin', true);

        $this->createReview([
            'book' => $this->createBook([]),
            'user' => $user,
        ]);

        $response = $this->delete('/users/1', [
            'headers' => $this->getAuthorizedHeaders('admin'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());

        $this->getEntityManager()->clear(); // Clear the em to force real db querying

        $user = $this->getEntityManager()->getRepository('BookRaterRaterBundle:User')
            ->findUserByUsername('mr_test');
        $this->assertNull($user);
        $review = $this->getEntityManager()->getRepository('BookRaterRaterBundle:Review')
            ->find(1);
        $this->assertNull($review);
    }

    /**
     * @throws \Exception
     */
    public function testFollowUserReviewLinks()
    {
        $user = $this->createUser('mr_test');
        $this->createUser('admin', 'admin', true);

        $this->createReview([
            'title' => 'My Most Favouritest Book',
            'user' => $user,
            'book' => $this->createBook([
                'title' => 'A Great Book',
            ]),
        ]);

        $response = $this->get('/users/mr_test', [
            'headers' => $this->getAuthorizedHeaders('admin'),
        ]);

        $this->asserter()->assertResponsePropertyExists($response, '_links.messages');
        $reviewsUri = $this->asserter()->readResponseProperty($response, '_links.reviews');

        $response = $this->client->get($reviewsUri); // Should be accessible without auth
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $this->asserter()->readResponseProperty($response, 'items[0]');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[0].title',
            'My Most Favouritest Book'
        );
    }

    /**
     * @throws \Exception
     */
    public function test404Exception()
    {
        $this->createUser('admin', 'admin', true);
        $response = $this->get('/users/mr_test', [
            'headers' => $this->getAuthorizedHeaders('admin'),
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No user found with identifier: "mr_test"');
    }

    /**
     * @throws \Exception
     */
    public function testDELETERequiresAdminAuthentication()
    {
        $this->createUser('mr_test'); // Not an admin
        $response = $this->delete('/users/1');
        $this->assertEquals(401, $response->getStatusCode());
    }

}
