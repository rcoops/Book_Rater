<?php

namespace BookRater\RaterBundle\Form\Api;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\AuthorType as ParentAuthorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends ParentAuthorType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('bookIds', EntityType::class, [
                'class' => Book::class,
                'property_path' => 'booksAuthored',
                'multiple' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'csrf_protection' => false,
            'is_api' => true,
        ]);
    }

}
