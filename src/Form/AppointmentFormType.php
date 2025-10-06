<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de début',
                'input' => 'datetime_immutable', // important
                'model_timezone' => 'Europe/Paris', // <<--- crée l'objet en Paris
                'view_timezone' => 'Europe/Paris', // et affiche Paris
                'with_seconds' => false,
            ])
            ->add('evaluatedPerson', EvaluatedPersonType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
