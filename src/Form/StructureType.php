<?php

namespace App\Form;

use App\Entity\Partner;
use App\Entity\Structure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('address')
            ->add('zipcode')
            ->add('city')
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
