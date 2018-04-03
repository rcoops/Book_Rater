<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Book;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewForSpecificBookType extends ReviewType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $book = $options['book'];
        parent::buildForm($builder, $options);
        $builder
            ->add('book', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'choices' => [$book],
            ]);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'specific_book' => true,
        ]);
    }

}