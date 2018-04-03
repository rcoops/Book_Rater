<?php

namespace BookRater\RaterBundle\Form\Api\Update;

use BookRater\RaterBundle\Form\Api\AuthorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateAuthorType extends AuthorType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['is_edit' => true]);
    }

}