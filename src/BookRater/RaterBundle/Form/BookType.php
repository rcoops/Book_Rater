<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Repository\AuthorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('publishDate', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'years' => range(date('Y'), 1500)
            ])
            ->add('edition')
//            ->add('authors', CollectionType::class, [
//                'entry_type' => AuthorType::class,
//                'entry_options' => ['label' => false],
//                'allow_add' => true,
//                'by_reference' => false,
//                'allow_delete'=> true,
//            ])
            ->add('authors', EntityType::class, [
                'class' => 'BookRater\RaterBundle\Entity\Author',
                'choice_label' => 'displayName',
                'query_builder' => function(AuthorRepository $ar) {
                    return $ar->findAllOrderedByName();
                },
                'placeholder' => 'Choose Author...'
            ])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']]);
//            ->add('addAuthor', SubmitType::class, ['attr' => ['class' => 'btn btn-success'], 'label' => 'Add Author']);
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
