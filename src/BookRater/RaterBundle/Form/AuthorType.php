<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractWebType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'required' => true,
                'disabled' => $options['is_edit'], // do not allow changes on update
            ])
            ->add('initial')
            ->add('firstName', TextType::class, [
                'required' => true,
                'disabled' => $options['is_edit'], // do not allow changes on update
            ])
            ->add('booksAuthored', EntityType::class, [
                'class' => Book::class,
                'by_reference' => false,
                'multiple' => true,
                'query_builder' => function (BookRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                },
                'disabled' => $options['is_api'],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'disabled' => $options['is_api'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Author::class,
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
