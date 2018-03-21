<?php
/**
 * Created by PhpStorm.
 * User: rick
 * Date: 21/03/18
 * Time: 19:49
 */

namespace Tests\BookRaterRaterBundle\Controller\Api;


use Tests\BookRaterRaterBundle\ApiTestCase;

class AuthorControllerTest extends ApiTestCase
{

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testPOSTAuthor()
    {
        $this->createUser('mr_test', 'MostSecretestPassword');

        $data = [
            'firstName' => 'Bruce',
            'lastName' => 'Wayne',
            'initial' => '',
        ];
    }

//    /**
//     * @throws \Exception
//     */
//    public function testValidationErrors()
//    {
//        $data = [
//            'avatarNumber' => 2,
//            'tagLine' => 'I\'m from a test!'
//        ];
//
//        // 1) Create a programmer resource
//        $response = $this->client->post('/api/authors', [
//            'body' => json_encode($data),
//            'headers' => $this->getAuthorizedHeaders('weaverryan')
//        ]);
//
//        $this->assertEquals(400, $response->getStatusCode());
//        $this->asserter()->assertResponsePropertiesExist($response, array(
//            'type',
//            'title',
//            'errors',
//        ));
//        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
//        $this->asserter()->assertResponsePropertyEquals($response, 'errors.nickname[0]', 'Please enter a clever nickname');
//        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
//        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
//    }

}