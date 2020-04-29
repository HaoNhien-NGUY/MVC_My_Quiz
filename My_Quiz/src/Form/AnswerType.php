<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'answer',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'answer1' => '1',
                        'answer2' => '2',
                        'answer3' => '3',
                        'answer4' => '4'
                    ),
                    'choices_as_values' => true, 'multiple' => false, 'expanded' => true
                )
            )
            ->add('Submit', SubmitType::class);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
