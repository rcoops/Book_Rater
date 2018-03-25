<?php

namespace BookRater\RaterBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFormEventSubscriber implements EventSubscriberInterface
{

    /**
     * Parses date string format 'd-m-Y' on rest form submission to array format recognisable by symfony forms.
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['publishDate'])) {
            $filter = ['day', 'month', 'year'];
            $data['publishDate'] = array_filter(
                date_parse($data['publishDate']),
                function ($key) use ($filter) {
                    return in_array($key, $filter);
                },
                ARRAY_FILTER_USE_KEY);;
        }
        $event->setData($data);
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SUBMIT => 'preSubmit'];
    }

}
