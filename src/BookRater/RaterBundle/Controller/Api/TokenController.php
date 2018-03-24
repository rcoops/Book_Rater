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
     *
     * @SWG\Post(
     *     produces={"application/json"},
     *     parameters={
     *         @SWG\Parameter(in="header", name="Authorization", type="string", description="Basic auth"),
     *     },
     *     security={@SWG\SecurityScheme(type="basic", name="Authorization")},
     *     responses={@SWG\Response(response="201", description="A JWT Bearer token to be used for authorization.")}
     * )
     */
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
