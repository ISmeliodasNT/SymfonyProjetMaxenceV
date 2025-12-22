<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'label' => 'label_email'
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'texte_inscription_mdp',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'erreur_mdp_vide',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'erreur_mdp_court',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('nom', null, [
                'label' => 'label_nom'
            ])
            ->add('prenom', null, [
                'label' => 'label_prenom'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'texte_inscription_termes',
                'constraints' => [
                    new IsTrue([
                        'message' => 'erreur_termes',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}