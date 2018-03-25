<?php

namespace BookRater\RaterBundle\Form\Api;

use Symfony\Component\OptionsResolver\OptionsResolver;

trait ApiTypeTrait
{

    public function configureApiOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'is_api' => true,
        ]);
    }

}