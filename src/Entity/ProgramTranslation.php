<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[Orm\Entity]
#[ORM\Table(name: 'program_translation')]
#[ORM\UniqueConstraint(name: 'lookup_unique_idx', columns: ['locale', 'object_id', 'field'])]
class ProgramTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: "translations")]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: "CASCADE")]
    protected $object;

    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    public function setObject($object): void
    {
        $this->object = $object;
    }
}