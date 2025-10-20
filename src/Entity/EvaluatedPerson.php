<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class EvaluatedPerson
{
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 100)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 100)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $patronyms = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull()]
    #[Assert\LessThan('today')]
    private ?\DateTimeInterface $birthdate = null;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPatronyms(): ?string
    {
        return $this->patronyms;
    }

    public function setPatronyms(?string $patronyms): static
    {
        $this->patronyms = $patronyms;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }
}
