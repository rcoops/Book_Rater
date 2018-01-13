<?php

namespace BookRater\RaterBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class HomeController extends EntityController
{

    public function indexAction(Request $request)
    {
        $latestReviews = $this->entityManager
            ->getRepository('BookRaterRaterBundle:Review')
            ->getLatestQuery();
        $pagination = $this->paginate($latestReviews, $request);
        return $this->render('BookRaterRaterBundle:Home:index.html.twig',
            ['pagination' => $pagination]);
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
