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
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('zipcode', NumberType::class,  [
                'label' => 'Code postale',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 5,
                    ]),
                ]])
            ->add('city', TextType::class ,  [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('partner', EntityType::class, [
                    'mapped' => false,
                    'class' => Partner::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Partenaire',
                    'label' => 'Partenaire'
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
