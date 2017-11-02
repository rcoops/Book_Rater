<?php

namespace BookRater\RaterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function indexAction()
    {
        return $this->render('BookRaterRaterBundle:Page:index.html.twig', array(
            // ...
        ));
    }

}
