<?php

namespace App\Form;

use App\Entity\Souris;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SourisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('prix')
            ->add('description')
            ->add('stock')
            ->add('status')
            ->add('connectivite')
            ->add('NbBoutons')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Souris::class,
        ]);
    }
}
