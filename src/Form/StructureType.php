<?php

namespace App\Form;

use App\Entity\Partner;
use App\Entity\Structure;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StructureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse :',
                'label_attr' => [
                    'class' => 'col-sm-2 col-form-label',
                ],
                'attr' => ['class' => 'form-control form-input'],
            ])
            ->add('zipcode', NumberType::class,  [
                'label' => 'Code postal :',
                'label_attr' => [
                    'class' => 'col-sm-2 col-form-label',
                ],
                'attr' => ['class' => 'form-control form-input'],
                'constraints' => [
                    new Length([
                        'max' => 5,
                    ]),
                ]])
            ->add('city', TextType::class ,  [
                'label' => 'Ville :',
                'label_attr' => [
                    'class' => 'col-sm-2 col-form-label',
                ],
                'attr' => ['class' => 'form-control form-input'],
            ])
            ->add('partner', EntityType::class, [
                    'mapped' => false,
                    'class' => Partner::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Partenaire ...',
                    'label' => 'Partenaire :',
                    'label_attr' => [
                        'class' => 'col-sm-2 col-form-label',
                    ],
                    'attr' => [
                        'class' => 'form-select'
                    ],
                ]
            );
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
        ]);
    }
}
