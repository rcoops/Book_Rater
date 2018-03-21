<?php

namespace BookRater\RaterBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Request;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use BookRater\RaterBundle\Entity\Author;

class AuthorController extends BaseController
{

    /**
     * @Route("/authors/")
     * @Method("POST")
     * @SWG\Response(
     *     response=201,
     *     description="Returns the rewards of an user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Author::class)
     *     )
     * )
     * @param Request $request
     */
    public function newAction(Request $request)
    {

    }

}
