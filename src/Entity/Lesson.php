<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    public const STATUS_DONE = 'DONE';
    public const STATUS_STUDY = 'STUDY';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $status = 'STUDY';

    #[ORM\Column]
    private ?\DateTimeImmutable $studiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    private ?Courses $courses = null;

    public function __construct()
    {
        $this->studiedAt = new \DateTimeImmutable();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStudiedAt(): ?\DateTimeImmutable
    {
        return $this->studiedAt;
    }

    public function setStudiedAt(\DateTimeImmutable $studiedAt): static
    {
        $this->studiedAt = $studiedAt;

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

    public function getCourses(): ?Courses
    {
        return $this->courses;
    }

    public function setCourses(?Courses $courses): static
    {
        $this->courses = $courses;

        return $this;
    }
}
