<?php

namespace App\Entity;

use App\Repository\QuizResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizResultRepository::class)]
class QuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    private ?Sections $section = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

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

    public function getSection(): ?Sections
    {
        return $this->section;
    }

    public function setSection(?Sections $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }
}
