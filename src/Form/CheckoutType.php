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
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Utilisateur $user */
        $user = $options['user'];

        $builder
            ->add('adresseLivraison', EntityType::class, [
                'class' => Address::class,
                'label' => 'checkout_titre_livraison',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('a')
                        ->where('a.user = :user')
                        ->setParameter('user', $user);
                },
                'choice_label' => function (Address $a) {
                    return $a->getRue() . ' - ' . $a->getVille() . ' (' . $a->getCodePostal() . ')';
                },
            ])
            ->add('cartePaiement', EntityType::class, [
                'class' => CreditCard::class,
                'label' => 'checkout_titre_paiement',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user);
                },
                'choice_label' => function (CreditCard $c) {
                    return $this->translator->trans('checkout_format_carte', [
                        '%number%' => substr($c->getNumber(), -4),
                        '%date%' => $c->getExpirationDate()
                    ]);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null, 
        ]);
        
        $resolver->setAllowedTypes('user', Utilisateur::class);
    }
}