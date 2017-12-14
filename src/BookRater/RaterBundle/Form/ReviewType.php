<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookReviewed', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'query_builder' => function(BookRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                },
                'placeholder' => 'Choose Book...'
            ])
            ->add('rating', IntegerType::class, ['attr' => ['min' => 0, 'max' => 5]])
            ->add('reviewComments')
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn-primary']]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BookRater\RaterBundle\Entity\Review'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_review';
    }


}
