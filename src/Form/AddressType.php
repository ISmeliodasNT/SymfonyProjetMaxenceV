<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType; // Optionnel si tu veux gérer les pays
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
                'label' => 'Rue et numéro',
                'attr' => ['placeholder' => 'Ex: 10 rue de la Paix'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner la rue']),
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['placeholder' => 'Ex: 75000'],
                'constraints' => [
                    new NotBlank(['message' => 'Le code postal est obligatoire']),
                    new Length(['min' => 4, 'max' => 10, 'minMessage' => 'Code postal trop court'])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Ex: Paris'],
                'constraints' => [
                    new NotBlank(['message' => 'La ville est obligatoire']),
                ]
            ])
            // Optionnel : si tu veux ajouter le pays
            // ->add('pays', CountryType::class, [
            //     'label' => 'Pays',
            //     'preferred_choices' => ['FR', 'BE', 'CH', 'CA'],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}