<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Message;
use BookRater\RaterBundle\Form\MessageType;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends EntityController
{

    public function indexAction(Request $request)
    {
        dump(now()); die;
        $latestReviews = $this->entityManager
            ->getRepository('BookRaterRaterBundle:Review')
            ->getLatestByFilter();
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
        return $this->render('BookRaterRaterBundle:Message:contact.html.twig');
    }

}
