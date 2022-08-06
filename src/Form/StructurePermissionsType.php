<?php

namespace App\Form;

use App\Entity\Permissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructurePermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newsletter', CheckboxType::class, [
                'label' => 'Envoi Newsletter',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('planning_management', CheckboxType::class, [
                'label' => 'Gestion des plannings',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('drink_sales', CheckboxType::class, [
                'label' => 'Vente de boissons',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('video_courses', CheckboxType::class, [
                'label' => 'Accès cours vidéos',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('prospect_reminders', CheckboxType::class, [
                'label' => 'Relance des prospects',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('sponsorship', CheckboxType::class, [
                'label' => 'Parrainage',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('free_wifi', CheckboxType::class, [
                'label' => 'Wifi gratuit',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
            ->add('flexible_hours', CheckboxType::class, [
                'label' => 'Flexibilité des horaires',
                'required' => false,
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permissions::class,
        ]);
    }
}
