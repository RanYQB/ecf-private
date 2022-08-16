<?php

namespace App\Form;

use App\Entity\Permissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newsletter', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Envoi Newsletter',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('planning_management', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Gestion des plannings',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('drink_sales', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Vente de boissons',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('video_courses', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Accès cours vidéos',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('prospect_reminders', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Relance des prospects',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('sponsorship', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Parrainage',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('free_wifi', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Wifi gratuit',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
            ])
            ->add('flexible_hours', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input my-checkbox',
                ],
                'label' => 'Flexibilité des horaires',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label',
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
