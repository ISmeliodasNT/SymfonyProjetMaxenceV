<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'admin_user_email'])
            ->add('nom', TextType::class, ['label' => 'admin_user_nom'])
            ->add('prenom', TextType::class, ['label' => 'admin_user_prenom'])
            
            ->add('roles', ChoiceType::class, [
                'label' => 'admin_user_roles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])

            ->add('plainPassword', PasswordType::class, [
                'label' => 'admin_user_password',
                'mapped' => false,
                'required' => $options['is_new'],
                'attr' => ['autocomplete' => 'new-password'],
                'help' => $options['is_new'] ? null : 'admin_user_password_help',
            ])

            ->add('adresses', CollectionType::class, [
                'entry_type' => AddressType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,  
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false
            ])

            ->add('creditCards', CollectionType::class, [
                'entry_type' => CreditCardType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'is_new' => false,
        ]);
    }
}