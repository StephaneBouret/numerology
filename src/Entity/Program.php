<?php

namespace App\Entity;

use App\Repository\ProgramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProgramRepository::class)]
#[Vich\Uploadable]
class Program
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du programme est obligatoire !")]
    #[Assert\Length(min: 3, minMessage: "Le nom du programme doit avoir au moins {{ limit }} caractères", max: 30, maxMessage: "Le nom du programme ne peut excéder {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire !")]
    private ?string $description = null;

    #[Assert\Image(
        maxSize: '2M',
        maxSizeMessage: 'L\'image est trop lourde ({{ size }} {{ suffix }}). 
        Le maximum autorisé est {{ limit }} {{ suffix }}',
        minWidth: 400,
        minWidthMessage: 'La largeur de l\'image est trop petite ({{ width }}px).
        Le minimum est {{ min_width }}px.',
        minHeight: 400,
        minHeightMessage: 'La hauteur est trop faible ({{ height }}px).
        Le minimum est {{ min_height }}px.',
        mimeTypes: [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ],
        mimeTypesMessage: 'Le type MIME du fichier n\'est pas valide ({{ type }}). Les formats autorisés sont {{ types }}'
    )]
    #[Vich\UploadableField(mapping: 'programs_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $price = null;

    /**
     * @var Collection<int, Sections>
     */
    #[ORM\OneToMany(targetEntity: Sections::class, mappedBy: 'program')]
    private Collection $sections;

    /**
     * @var Collection<int, Courses>
     */
    #[ORM\OneToMany(targetEntity: Courses::class, mappedBy: 'program')]
    private Collection $courses;

    /**
     * @var Collection<int, Purchase>
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'program')]
    private Collection $purchases;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $satisfiedTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $satisfiedContent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $showTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $showContent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detailTitle = null;

    /**
     * @var Collection<int, ProgramDetail>
     */
    #[ORM\OneToMany(targetEntity: ProgramDetail::class, mappedBy: 'program', cascade: ['persist', 'remove'])]
    private Collection $details;

    /**
     * @var Collection<int, Certificate>
     */
    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'program')]
    private Collection $certificates;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->purchases = new ArrayCollection();
        $this->details = new ArrayCollection();
        $this->certificates = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

            /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

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

    /**
     * @return Collection<int, Sections>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Sections $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setProgram($this);
        }

        return $this;
    }

    public function removeSection(Sections $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getProgram() === $this) {
                $section->setProgram(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Courses>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Courses $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setProgram($this);
        }

        return $this;
    }

    public function removeCourse(Courses $course): static
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getProgram() === $this) {
                $course->setProgram(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setProgram($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getProgram() === $this) {
                $purchase->setProgram(null);
            }
        }

        return $this;
    }

    public function getSatisfiedTitle(): ?string
    {
        return $this->satisfiedTitle;
    }

    public function setSatisfiedTitle(?string $satisfiedTitle): static
    {
        $this->satisfiedTitle = $satisfiedTitle;

        return $this;
    }

    public function getSatisfiedContent(): ?string
    {
        return $this->satisfiedContent;
    }

    public function setSatisfiedContent(?string $satisfiedContent): static
    {
        $this->satisfiedContent = $satisfiedContent;

        return $this;
    }

    public function getShowTitle(): ?string
    {
        return $this->showTitle;
    }

    public function setShowTitle(?string $showTitle): static
    {
        $this->showTitle = $showTitle;

        return $this;
    }

    public function getShowContent(): ?string
    {
        return $this->showContent;
    }

    public function setShowContent(?string $showContent): static
    {
        $this->showContent = $showContent;

        return $this;
    }

    public function getDetailTitle(): ?string
    {
        return $this->detailTitle;
    }

    public function setDetailTitle(?string $detailTitle): static
    {
        $this->detailTitle = $detailTitle;

        return $this;
    }

    /**
     * @return Collection<int, ProgramDetail>
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(ProgramDetail $detail): static
    {
        if (!$this->details->contains($detail)) {
            $this->details->add($detail);
            $detail->setProgram($this);
        }

        return $this;
    }

    public function removeDetail(ProgramDetail $detail): static
    {
        if ($this->details->removeElement($detail)) {
            // set the owning side to null (unless already changed)
            if ($detail->getProgram() === $this) {
                $detail->setProgram(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): static
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setProgram($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): static
    {
        if ($this->certificates->removeElement($certificate)) {
            // set the owning side to null (unless already changed)
            if ($certificate->getProgram() === $this) {
                $certificate->setProgram(null);
            }
        }

        return $this;
    }
}
