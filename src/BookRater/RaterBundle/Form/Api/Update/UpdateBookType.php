<?php

namespace BookRater\RaterBundle\Form\Api\Update;

use BookRater\RaterBundle\Form\Api\BookType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateBookType extends BookType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['is_edit' => true]);
    }

}
