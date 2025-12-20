<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\CreditCard;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère l'utilisateur passé en option
        /** @var Utilisateur $user */
        $user = $options['user'];

        $builder
            ->add('adresseLivraison', EntityType::class, [
                'class' => Address::class,
                'label' => 'Adresse de livraison',
                'required' => true,
                'expanded' => true, // Affiche des boutons radio (plus joli)
                'multiple' => false,
                // TRÈS IMPORTANT : On ne veut voir que SES adresses à lui
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('a')
                        ->where('a.user = :user')
                        ->setParameter('user', $user);
                },
                // Ce qu'on affiche dans le choix
                'choice_label' => function (Address $a) {
                    return $a->getRue() . ' - ' . $a->getVille() . ' (' . $a->getCodePostal() . ')';
                },
            ])
            ->add('cartePaiement', EntityType::class, [
                'class' => CreditCard::class,
                'label' => 'Moyen de paiement',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                // TRÈS IMPORTANT : On ne veut voir que SES cartes
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user);
                },
                'choice_label' => function (CreditCard $c) {
                    // On masque le début de la carte pour la sécurité visuelle
                    return 'Carte finissant par **** ' . substr($c->getNumber(), -4) . ' (Exp: ' . $c->getExpirationDate() . ')';
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // On force le fait de devoir passer un utilisateur à ce formulaire
            'user' => null, 
        ]);
        
        // On valide que l'option 'user' est bien une instance de ton entité Utilisateur
        $resolver->setAllowedTypes('user', Utilisateur::class);
    }
}