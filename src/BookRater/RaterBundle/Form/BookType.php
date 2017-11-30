<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Repository\AuthorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title')
            ->add('isbn')
            ->add('isbn13')
            ->add('publisher')
            ->add('publishDate')
            ->add('edition')
            ->add('authors', EntityType::class, [
                'class' => 'BookRater\RaterBundle\Entity\Author',
                'choice_label' => 'displayName',
                'query_builder' => function(AuthorRepository $ar) {
                    return $ar->findAllOrderedByName();
                },
                'placeholder' => 'Choose Author...'
            ]);
//            ->add('createAuthor', ButtonType::class, ['attr' => ['class' => 'btn btn-primary']]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'authors' => ['Create...'],
                'data_class' => 'BookRater\RaterBundle\Entity\Book'
            ])
            ->setAllowedTypes('authors', ['array']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_book';
    }


}
