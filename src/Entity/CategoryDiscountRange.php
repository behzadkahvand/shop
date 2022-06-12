<?php

namespace App\Entity;

use App\Repository\CategoryDiscountRangeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CategoryDiscountRangeRepository::class)
 * @ORM\Table(name="category_discount_ranges")
 */
class CategoryDiscountRange
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"category.discount.index"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"category.discount.index"})
     */
    private $isBounded = true;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Assert\Range(min=0, max=100)
     *
     * @Groups({"category.discount.index"})
     */
    private $minDiscount = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Assert\Range(min=0, max=100)
     * @Assert\GreaterThanOrEqual(
     *     propertyPath="minDiscount",
     *     message="Max discount must be greater than or equal to min discount"
     * )
     *
     * @Groups({"category.discount.index"})
     */
    private $maxDiscount = 0;

    /**
     * @ORM\OneToOne(targetEntity=Category::class, inversedBy="discountRange", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\Expression(
     *     "this.getCategory() && this.getCategory().isLeaf() === true",
     *     message="Selected category is not leaf"
     * )
     * @Assert\NotBlank()
     *
     * @Groups({"category.discount.index"})
     */
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsBounded(): bool
    {
        return $this->isBounded;
    }

    public function setIsBounded(bool $isBounded): self
    {
        $this->isBounded = $isBounded;

        return $this;
    }

    public function getMinDiscount(): ?int
    {
        return $this->minDiscount;
    }

    public function setMinDiscount(?int $minDiscount): self
    {
        $this->minDiscount = $minDiscount;

        return $this;
    }

    public function getMaxDiscount(): ?int
    {
        return $this->maxDiscount;
    }

    public function setMaxDiscount(?int $maxDiscount): self
    {
        $this->maxDiscount = $maxDiscount;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
