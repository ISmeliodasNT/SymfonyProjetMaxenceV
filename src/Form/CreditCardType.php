<?php

namespace App\Form;

use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['placeholder' => '0000 0000 0000 0000'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner le numéro de carte.']),
                    new Regex([
                        'pattern' => '/^[0-9\s]+$/', 
                        'message' => 'Le numéro ne doit contenir que des chiffres.'
                    ]),
                    // Vérifie la longueur (généralement entre 16 et 19 selon les cartes)
                    new Length([
                        'min' => 16,
                        'max' => 19,
                        'minMessage' => 'Le numéro est trop court (minimum {{ limit }} chiffres).',
                        'maxMessage' => 'Le numéro est trop long (maximum {{ limit }} chiffres).',
                    ]),
                ]
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Expiration (MM/YY)',
                'attr' => ['placeholder' => '12/25'],
                'constraints' => [
                    new NotBlank(['message' => 'La date est requise.']),
                    // Force le format 2 chiffres / 2 chiffres
                    new Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/',
                        'message' => 'Format invalide. Utilisez le format MM/YY (ex: 12/25).'
                    ])
                ]
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVV',
                'attr' => ['maxlength' => 3, 'style' => 'width: 80px'],
                'constraints' => [
                    new NotBlank(['message' => 'Le code CVV est requis.']),
                    new Regex([
                        'pattern' => '/^\d{3}$/',
                        'message' => 'Le CVV doit être composé de 3 chiffres.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CreditCard::class]);
    }
}