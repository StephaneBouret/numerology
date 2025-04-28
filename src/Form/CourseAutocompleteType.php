<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseAutocompleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $programSlug = $options['program_slug'] ?? null;

        $builder->add('name', CoursesAutocompleteField::class, [
            'label' => false,
            'multiple' => false,
            'extra_options' => [
                'program_slug' => $programSlug,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'program_slug' => null,
        ]);
    }
}