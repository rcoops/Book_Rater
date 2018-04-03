<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Repository\AuthorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookType extends AbstractWebType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'disabled' => $options['is_edit'],
            ])// do not allow changes on update)
            ->add('isbn', TextType::class, [
                'disabled' => $options['is_edit'],
            ])
            ->add('isbn13')
            ->add('publisher')
            ->add('publishDate', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'years' => range(date('Y'), 1500),
            ])
            ->add('edition', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('authors', EntityType::class, [
                'class' => Author::class,
                'required' => false,
                'by_reference' => false,
                'multiple' => true,
                'query_builder' => function (AuthorRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                },
                'disabled' => $options['is_api'],
            ])
            ->add('googleBooksId', HiddenType::class, [
                'disabled' => $options['is_api'],
            ])
            ->add('googleBooksRating', HiddenType::class, [
                'disabled' => $options['is_api'],
            ])
            ->add('googleBooksReviewsUrl', HiddenType::class, [
                'disabled' => $options['is_api'],
            ])
            ->add('googleBooksUrl', HiddenType::class, [
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
            'data_class' => Book::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_book';
    }


}
