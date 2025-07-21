<?php

namespace App\Entity;

use App\Repository\AppointmentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentTypeRepository::class)]
class AppointmentType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $duration; // minutes

    #[ORM\Column(nullable: true)]
    private ?int $minAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxAge = null;

    #[ORM\Column]
    private ?int $price = null; // centimes

    #[ORM\Column]
    private ?int $participants = 1;

    #[ORM\Column]
    private ?bool $isPack = false;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?AppointmentType $prerequisite = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'type')]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): static
    {
        $this->minAge = $minAge;

        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getParticipants(): ?int
    {
        return $this->participants;
    }

    public function setParticipants(int $participants): static
    {
        $this->participants = $participants;

        return $this;
    }

    public function isPack(): ?bool
    {
        return $this->isPack;
    }

    public function setIsPack(bool $isPack): static
    {
        $this->isPack = $isPack;

        return $this;
    }

    public function getPrerequisite(): ?AppointmentType
    {
        return $this->prerequisite;
    }

    public function setPrerequisite(?AppointmentType $prerequisite): static
    {
        $this->prerequisite = $prerequisite;

        return $this;
    }

    public function getDurationLabel(): string
    {
        return $this->duration >= 60
            ? sprintf('%dh%02d', floor($this->duration / 60), $this->duration % 60)
            : $this->duration . ' min';
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setType($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getType() === $this) {
                $appointment->setType(null);
            }
        }

        return $this;
    }
}
