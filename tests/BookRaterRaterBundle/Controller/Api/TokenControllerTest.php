<?php

namespace Tests\BookRaterRaterBundle\Controller\Api;

use Tests\BookRaterRaterBundle\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{

    /**
     * @throws \Exception
     */
    public function testPOSTCreateToken()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $response = $this->post('/tokens', [
            'auth' => ['mr_test', 'MostSecretestPassword']
        ]);
        $this->assertEquals(201, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists(
            $response,
            'token'
        );
    }

    /**
     * @throws \Exception
     */
    public function testPOSTTokenInvalidCredentials()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $response = $this->post('/tokens', [
            'auth' => ['mr_test', 'NotAsSecretestPassword']
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Invalid credentials.');
    }

}
