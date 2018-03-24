<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractWebType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('book', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'query_builder' => function (BookRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                },
                'placeholder' => 'Choose Book...',
                'disabled' => $options['is_api'],
            ])
            ->add('rating', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                    'onchange' => "updateNum(this.value)",
                ]
            ])
            ->add('ratingNum', TextType::class, [
                'data' => 3,
                'mapped' => false,
                'label_format' => ' ',
                'disabled' => true,
            ])
            ->add('comments')
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
            'data_class' => Review::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_review';
    }

}
