<?php

namespace BookRater\RaterBundle\Form\Api;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $programmers = $options['programmers'];

        $builder->add('lastName', TextType::class, ['required' => true])
            ->add('initial', TextType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => true])
            ->add('booksAuthoredIds', EntityType::class, [
                'class' => 'BookRater\RaterBundle\Entity\Book',
                'by_reference' => false,
                'property_path' => 'booksAuthored',
                'multiple' => true,
                'query_builder' => function(BookRepository $ar) use ($programmers) {
                    return $ar->findAllOrderedByNameQB();
                },
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Author::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_author';
    }


}
