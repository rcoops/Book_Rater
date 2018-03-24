<?php
/**
 * Created by PhpStorm.
 * User: rick
 * Date: 24/03/18
 * Time: 17:37
 */

namespace BookRater\RaterBundle\Form\Api;
use BookRater\RaterBundle\Entity\Author;
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
            ]);

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
