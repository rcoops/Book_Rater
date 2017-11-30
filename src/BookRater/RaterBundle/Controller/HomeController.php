<?php

namespace BookRater\RaterBundle\Controller;

class HomeController extends EntityController
{

    public function indexAction()
    {
        $latestReviews = $this->entityManager
            ->getRepository('BookRaterRaterBundle:Review')
            ->getLatest();
        return $this->render('BookRaterRaterBundle:Home:index.html.twig',
            ['latestReviews' => $latestReviews]);
    }

    public function aboutAction()
    {
        return $this->render('BookRaterRaterBundle:Home:about.html.twig');
    }

    public function contactAction()
    {
        return $this->render('BookRaterRaterBundle:Home:contact.html.twig');
    }

}
