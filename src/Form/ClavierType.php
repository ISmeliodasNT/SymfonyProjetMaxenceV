<?php

namespace App\Form;

use App\Entity\Clavier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\Status;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ClavierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('prix')
            ->add('description')
            ->add('stock')
            ->add('status', EnumType::class, [
                'class' => Status::class,
                'choice_label' => fn(Status $s) => $s->name,
            ])
            ->add('switch')
            ->add('language')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clavier::class,
        ]);
    }
}
