<?php

namespace App\Form;

use App\Entity\Courses;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
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
            'choice_label' => fn (Courses $course) => $course->getName(),
            'searchable_fields' => ['name'],
            'max_results' => 10,
            'program_slug' => null,
            'extra_options' => [],
            'query_builder' => function (Options $options) {
                return function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('c')
                        ->join('c.section', 's')
                        ->join('s.program', 'p');

                    $slug = $options['extra_options']['program_slug'] ?? null;

                    if ($slug) {
                        $qb->where('p.slug = :slug')
                           ->setParameter('slug', $slug);
                    }

                    return $qb;
                };
            },
        ]);

        $resolver->setAllowedTypes('program_slug', ['null', 'string']);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
