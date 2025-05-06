<?php

namespace App\Entity;

use App\Repository\CoursesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CoursesRepository::class)]
#[Vich\Uploadable]
class Courses
{
    public const TYPE_TWIG = 'Twig';
    public const TYPE_VIDEO = 'Vidéo';
    public const TYPE_QUIZ = 'Quiz';
    public const TYPE_LINK = 'Lien';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du cours est obligatoire !")]
    #[Assert\Length(min: 10, max: 255, minMessage: 'Le nom du cours doit avoir au moins {{ limit }} caractères')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Vich\UploadableField(mapping: 'courses_files', fileNameProperty: 'partialFileName')]
    private ?File $partialFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $partialFileName = null;

    #[Assert\File(
        maxSize: '200M',
        extensions: ['mp4'],
        extensionsMessage: 'L\'extension du fichier n\'est pas valide ({{ extension }}). Les extensions autorisées sont {{ extensions }}.',
        mimeTypes: [
            'video/mp4'
        ],
        mimeTypesMessage: 'Le type MIME du fichier n\'est pas valide ({{ type }}). Les formats autorisés sont {{ types }}'
    )]
    #[Vich\UploadableField(mapping: 'courses_videos', fileNameProperty: 'videoName')]
    private ?File $videoFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $videoName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: [self::TYPE_TWIG, self::TYPE_VIDEO, self::TYPE_QUIZ, self::TYPE_LINK], message: "Le type de contenu sélectionné est invalide.")]
    private ?string $contentType = self::TYPE_TWIG;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    private ?Program $program = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    private ?Sections $section = null;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'courses', cascade: ['remove'])]
    private Collection $lessons;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\OneToMany(targetEntity: Comments::class, mappedBy: 'course', cascade: ['remove'])]
    private Collection $comments;

    #[ORM\Column]
    private ?bool $isFree = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

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

    public function getSection(): ?Sections
    {
        return $this->section;
    }

    public function setSection(?Sections $section): static
    {
        $this->section = $section;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $partialFile
     */
    public function setPartialFile(?File $partialFile = null): void
    {
        $this->partialFile = $partialFile;
        if (null !== $partialFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPartialFile(): ?File
    {
        return $this->partialFile;
    }

    public function setPartialFileName(?string $partialFileName): void
    {
        $this->partialFileName = $partialFileName;
    }

    public function getPartialFileName(): ?string
    {
        return $this->partialFileName;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $videoFile
     */
    public function setVideoFile(?File $videoFile = null): void
    {
        $this->videoFile = $videoFile;
        if (null !== $videoFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    public function setVideoName(?string $videoName): void
    {
        $this->videoName = $videoName;
    }

    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCourses($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getCourses() === $this) {
                $lesson->setCourses(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCourse($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCourse() === $this) {
                $comment->setCourse(null);
            }
        }

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->isFree;
    }

    public function setIsFree(bool $isFree): static
    {
        $this->isFree = $isFree;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function isCompletedByUser(User $user): bool
    {
        $userLessons = $this->lessons->filter(fn(Lesson $lesson) => $lesson->getUser() === $user);
    
        if ($userLessons->isEmpty()) {
            return false;
        }
    
        foreach ($userLessons as $lesson) {
            if ($lesson->getStatus() !== Lesson::STATUS_DONE) {
                return false;
            }
        }
    
        return true;
    }
}
