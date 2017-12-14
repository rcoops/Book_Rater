<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Repository\AuthorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('isbn', TextType::class,
                ['attr' => ['onInvalid' => "setCustomValidity('ISBN must be a 10 digit number')"]])
            ->add('isbn13', TextType::class,
                ['attr' => ['onInvalid' => "setCustomValidity('ISBN 13 must follow this format xxx-xxxxxxxxxx')"]]
            )
            ->add('publisher')
            ->add('publishDate', DateType::class, [
                'format' => 'dd-MM-yyyy',
                'years' => range(date('Y'), 1500)
            ])
            ->add('edition', IntegerType::class, ['attr' => ['min' => 0]])
            ->add('authors', EntityType::class, [
                'class' => 'BookRater\RaterBundle\Entity\Author',
                'choice_label' => 'displayName',
                'by_reference' => false,
                'multiple' => true,
                'query_builder' => function(AuthorRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                },
                'placeholder' => 'Choose Author...'
            ])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'BookRater\RaterBundle\Entity\Book'
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
