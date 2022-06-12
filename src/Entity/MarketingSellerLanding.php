<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\MarketingSellerLandingRepository;
use App\Validator\Mobile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="marketing_seller_landings")
 * @ORM\Entity(repositoryClass=MarketingSellerLandingRepository::class)
 */
class MarketingSellerLanding
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Mobile()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $ownershipType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $commodityDiversity;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class)
     * @ORM\JoinTable(name="marketing_seller_landings_categories")
     *
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one category",
     * )
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOwnershipType(): ?string
    {
        return $this->ownershipType;
    }

    public function setOwnershipType(string $ownershipType): self
    {
        $this->ownershipType = $ownershipType;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCommodityDiversity(): ?string
    {
        return $this->commodityDiversity;
    }

    public function setCommodityDiversity(string $commodityDiversity): self
    {
        $this->commodityDiversity = $commodityDiversity;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function __toString(): string
    {
        return collect($this->toArray())->filter()->transform(fn($value, $key) => $key == 'categories' ?
            "$key: " . collect($value)->map(fn(Category $category) => $category->getTitle())->implode(',') : "$key: $value")->implode(PHP_EOL);
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
