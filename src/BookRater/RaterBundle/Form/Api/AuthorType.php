<?php

namespace BookRater\RaterBundle\Form\Api;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\AuthorType as BaseAuthorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends BaseAuthorType
{

    use ApiTypeTrait;

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
        $this->configureApiOptions($resolver);
    }

}
