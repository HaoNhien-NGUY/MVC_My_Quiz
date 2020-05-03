<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quiz_name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'label' => false,
            ])
            ->add('question', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => false,
            ])
            ->add('reponse_1', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => false,
            ])
            ->add('reponse_2', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Wrong answer',
            ])
            ->add('reponse_3', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Wrong answer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
