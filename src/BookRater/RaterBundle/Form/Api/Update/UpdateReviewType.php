<?php

namespace BookRater\RaterBundle\Form\Api\Update;

use BookRater\RaterBundle\Form\Api\ApiTypeTrait;
use BookRater\RaterBundle\Form\ReviewType as BaseReviewType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateReviewType extends BaseReviewType
{

    use ApiTypeTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $this->configureApiOptions($resolver); // extends web form but with book disabled
    }

}
