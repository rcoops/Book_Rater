<?php

namespace BookRater\RaterBundle\Form\Api;
use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\EventListener\CustomFormEventSubscriber;
use BookRater\RaterBundle\Form\BookType as BaseBookType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends BaseBookType
{

    use ApiTypeTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('authorIds', EntityType::class, [
                'class' => Author::class,
                'property_path' => 'authors',
                'multiple' => true,
            ])
            ->addEventSubscriber(new CustomFormEventSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $this->configureApiOptions($resolver);
    }

}
