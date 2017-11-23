<?php

namespace BookRater\RaterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var \Blogger\BlogBundle\Repository\EntryRepository|\Doctrine\ORM\EntityRepository
     */
    private $entryRepository;

    public function indexAction()
    {
        return $this->render('BookRaterRaterBundle:Page:index.html.twig', array(
            // ...
        ));
    }

    public function aboutAction()
    {
        return $this->render('BookRaterRaterBundle:Page:about.html.twig', array(
            // ...
        ));
    }

    public function contactAction()
    {
        return $this->render('BookRaterRaterBundle:Page:contact.html.twig', array(
            // ...
        ));
    }

}
