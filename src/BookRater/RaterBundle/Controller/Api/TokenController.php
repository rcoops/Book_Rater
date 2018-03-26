<?php

namespace BookRater\RaterBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Swagger\Annotations as SWG;

class TokenController extends BaseApiController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws JWTEncodeFailureException
     *
     * @Rest\Post("/tokens")
     * @SWG\Post(
     *     tags={"Tokens"},
     *     produces={"application/json"},
     *     parameters={
     *         @SWG\Parameter(in="header", name="Authorization", type="string",
     *             description="Basic auth: ('Basic' followed by base 64 encoded '[username]:[password]' e.g. 'Basic dXNlcjpwYXNzd29yZA==')"
     *         ),
     *     },
     *     security={@SWG\SecurityScheme(type="basic", name="Authorization")},
     *     responses={
     *         @SWG\Response(
     *             response="201",
     *             description="A JWT Bearer token to be used for authorization.",
     *             schema=@SWG\Schema(
     *                type="object",
     *                properties={@SWG\Property(property="token", type="string")},
     *                example={"token":"eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZXhwIjoxNTIxOTE2MjExLCJpYXQiOjE1MjE5MTI2MTF9.TswXDTsKiR0xWVQcWpRLdz-UPsXjld9XklDRhpsVzwd_bz_MAvaBT8-1mwpuXiajU4lA5CLt_6I62yCHvslkSZU3goctgVVFwHllkk8f6bUBe1lFajArdkiuxQJ2TDAC9oaXNyxnFKCV8pwcOjSy9lOVxBN3nEP3O1Hij0fNA3AW2a4qGAgqpr5LiIOC9tWcZvag_iiokNcKn1230QdxKIZIaYnElCBhXWgRvxajKjiFj7IRIGub2ZsNkEz9fAlGc6Bbr5egXACgphUMwcHhEx2GYo2NrbY7t7DQJhtS5CULliD5wasXL23VgZwgosBf_DiY6MevvDS2tFJTOJUnK_LvCs2xBktBNxXddkAYVk3HhQ8TfMplLl6vq0y8unYSv_HlPKojg8ES8k2pq7_F6luvC5Tj_ChPJ3JuVd7nx7pbAMpRT6K6nla2Ck7W7IxCD-eB4RkaZIDUXaomSVTY_Q_MzFh1kqrhV4mBzLioTdgNoR3kdWQt-Q2XksEIg5ap9CaLZlLIBtpcZKNbNpLoy85QLwHp3WqCA0khJBhN53oBf8gDWnkFOFzIK7RqArpehzMcWV4KBzIFfH_dUgJcHPNSkqyq_1QKiBc1xtMhps4I7ygV3bhJ0CB0Tao103ODLc7ys8Jzvi9kejJ9tDXgFzK_1viBeg6vOYg-BMFpuSc"}
     *             )
     *         )
     *     }
     * )
     */
    //TODO additional (generic) responses
    public function newTokenAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:User')
            ->findOneBy(['username' => $request->getUser()]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->getPassword());

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);

        return new JsonResponse(['token' => $token], 201);
    }

    protected function getGroups()
    {
        return [];
    }

}
