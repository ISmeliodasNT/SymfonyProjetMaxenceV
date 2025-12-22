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
                'label' => 'texte_numero_carte',
                'attr' => ['placeholder' => '0000 0000 0000 0000'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_erreur_carte_vide']),
                    new Regex([
                        'pattern' => '/^[0-9\s]+$/', 
                        'message' => 'texte_erreur_carte_chiffre'
                    ]),
                    new Length([
                        'min' => 16,
                        'max' => 19,
                        'minMessage' => 'texte_erreur_carte_chiffre_court',
                        'maxMessage' => 'texte_erreur_carte_chiffre_long',
                    ]),
                ]
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'texte_expiration_carte',
                'attr' => ['placeholder' => '12/25'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_erreur_expiration_vide']),
                    new Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/',
                        'message' => 'texte_erreur_expiration_format'
                    ])
                ]
            ])
            ->add('cvv', TextType::class, [
                'label' => 'texte_code_carte',
                'attr' => ['maxlength' => 3, 'style' => 'width: 80px'],
                'constraints' => [
                    new NotBlank(['message' => 'texte_erreur_code_vide']),
                    new Regex([
                        'pattern' => '/^\d{3}$/',
                        'message' => 'texte_erreur_code_format'
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