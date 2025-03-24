<?php

namespace App\Form;

use App\Entity\Courses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class CoursesAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Courses::class,
            'placeholder' => 'Choisissez un cours',
            // 'choice_label' => function(Courses $courses): string {
            //     return $courses->getName();
            // },
            'choice_label' => function (Courses $courses): string {
                return sprintf(
                    $courses->getName(),
                    $courses->getProgram()->getSlug(),
                    $courses->getSection()->getSlug(),
                    $courses->getSlug()
                );
            },
            'security' => 'ROLE_USER',
            'max_results' => 10,
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
