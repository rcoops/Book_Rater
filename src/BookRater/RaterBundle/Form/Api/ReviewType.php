<?php

namespace BookRater\RaterBundle\Form\Api;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\ReviewType as BaseReviewType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends BaseReviewType
{
    use ApiTypeTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('bookId', EntityType::class, [
                'class' => Book::class,
                'property_path' => 'book',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $this->configureApiOptions($resolver);
    }

}
