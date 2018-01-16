<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Author;
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
        $builder->add('title', TextType::class)
            ->add('isbn', TextType::class)
            ->add('isbn13', TextType::class)
            ->add('publisher')
            ->add('publishDate', DateType::class, [
                'format' => 'dd-MM-yyyy',
                'years' => range(date('Y'), 1500)
            ])
            ->add('edition', IntegerType::class, ['attr' => ['min' => 0]])
            ->add('authors', EntityType::class, [
                'class' => Author::class,
                'required' => false,
                'by_reference' => false,
                'multiple' => true,
                'query_builder' => function(AuthorRepository $ar) {
                    return $ar->findAllOrderedByNameQB();
                }
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
