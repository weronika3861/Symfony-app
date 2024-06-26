<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks()
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"item", "list", "export"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Type("string")
     * @Assert\Length(min=5)
     * @Groups({"item", "list", "export"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type("string")
     * @Groups({"item"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"item", "export"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"item", "export"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=ProductCategory::class, inversedBy="products", cascade={"persist"})
     * @Groups({"item", "export"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=ProductImage::class, mappedBy="product")
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity=ProductImage::class, cascade={"persist"})
     * ORM\JoinColumn(onDelete="SET NULL")
     */
    private $mainImage;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection|ProductCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(ProductCategory $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }
    }

    public function removeCategory(ProductCategory $category): void
    {
        $this->categories->removeElement($category);
    }

    public function removeCategories(): void
    {
        $this->categories->clear();
    }

    /**
     * @return Collection|ProductImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @return string[]
     */
    public function getImagesNames(): array
    {
        $names = [];

        /** @var ProductImage $image */
        foreach ($this->getImages() as $image) {
            $names[] = $image->getFilename();
        }

        return $names;
    }

    public function addImage(ProductImage $image): void
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduct($this);
        }
    }

    public function removeImage(ProductImage $image): void
    {
        $this->images->removeElement($image);
    }

    public function getMainImage(): ?ProductImage
    {
        return $this->mainImage;
    }

    public function setMainImage(?ProductImage $mainImage): void
    {
        $this->mainImage = $mainImage;
    }
}
