<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PurchaseRepository;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Purchase
{
    public const STATUS_PENDING = 'EN ATTENTE';
    public const STATUS_PAID = 'PAYEE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $status = 'EN ATTENTE';

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Program $program = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $number = null;

    #[ORM\PrePersist]
    public function prePersist()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTimeImmutable();
        }
        if (empty($this->amount)) {
            $this->amount = $this->program->getPrice();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
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

    public function getProgram(): ?Program
    {
        return $this->program;
    }

    public function setProgram(?Program $program): static
    {
        $this->program = $program;

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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }
}
