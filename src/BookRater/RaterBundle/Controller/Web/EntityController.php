<?php


namespace BookRater\RaterBundle\Controller\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\Tools\Pagination\Paginator;

class EntityController extends Controller
{

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function paginate($query, $request)
    {
        return $this->get('knp_paginator')->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
    }

}
