<?php

namespace BookRater\RaterBundle\Form\Api;
use BookRater\RaterBundle\Form\MessageType as BaseMessageType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends BaseMessageType
{

    use ApiTypeTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $this->configureApiOptions($resolver);
    }

}
