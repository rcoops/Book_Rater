<?php

namespace BookRater\RedirectBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RedirectFOSUserSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onRegistrationSuccess(FilterUserResponseEvent  $event)
    {
        // Will retrieve a url attempted to be accessed before auth, from the session
        $url = $this->getTargetPath($event->getRequest()->getSession(), 'main');

        if (!$url)
        {
            $url = $this->router->generate('bookrater_home');
        }

        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }

    public function onPasswordChange(FormEvent $event)
    {
        $url = $this->router->generate('bookrater_home');
        $event->setResponse(new RedirectResponse($url));
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChange',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationSuccess'
        ];
    }

}