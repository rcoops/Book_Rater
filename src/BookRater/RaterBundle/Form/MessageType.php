<?php

namespace BookRater\RaterBundle\Form;

use BookRater\RaterBundle\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, ['label'=> 'Subject','attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('message', TextareaType::class, ['label'=> 'Message','attr' => ['class' => 'form-control']])
            ->add('Send', SubmitType::class, ['attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:15px']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookrater_raterbundle_contact';
    }

}