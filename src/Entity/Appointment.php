<?php

namespace App\Entity;

use App\Entity\EvaluatedPerson;
use App\Enum\AppointmentStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AppointmentRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppointmentType $type = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(enumType: AppointmentStatus::class)]
    private AppointmentStatus $status = AppointmentStatus::PENDING;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Embedded(class: EvaluatedPerson::class)]
    #[Assert\Valid()]
    private EvaluatedPerson $evaluatedPerson;

    #[ORM\Embedded(class: EvaluatedPerson::class, columnPrefix: 'partner_')]
    #[Assert\Valid()]
    private ?EvaluatedPerson $partner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isSent = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->evaluatedPerson = new EvaluatedPerson();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?AppointmentType
    {
        return $this->type;
    }

    public function setType(?AppointmentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getStatus(): AppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(AppointmentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEvaluatedPerson(): EvaluatedPerson
    {
        return $this->evaluatedPerson;
    }

    public function setEvaluatedPerson(EvaluatedPerson $evaluatedPerson): static
    {
        $this->evaluatedPerson = $evaluatedPerson;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): static
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getPartner(): ?EvaluatedPerson
    {
        return $this->partner;
    }

    public function setPartner(?EvaluatedPerson $partner): static
    {
        $this->partner = $partner;

        return $this;
    }

    public function isSent(): ?bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): static
    {
        $this->isSent = $isSent;

        return $this;
    }
}
