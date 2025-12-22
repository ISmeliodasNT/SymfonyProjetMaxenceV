<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'texte_adresse_rue',
                'attr' => ['placeholder' => 'texte_adresse_exemple_rue'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_adresse_erreur_rue_vide']),
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'texte_adresse_code_postal',
                'attr' => ['placeholder' => 'texte_adresse_exemple_code_postal'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_adresse_erreur_code_postal_vide']),
                    new Length(['min' => 4, 'max' => 10, 'minMessage' => 'texte_adresse_erreur_code_postal_court'])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'texte_adresse_ville',
                'attr' => ['placeholder' => 'tests_adresse_exemple_ville'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_adresse_erreur_ville_vide']),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}