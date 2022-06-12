<?php

namespace App\Entity;

use App\Repository\CategoryBrandSellerProductOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="category_brand_seller_product_options", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"category_id", "brand_id", "product_option_id"})
 * })
 *
 * @ORM\Entity(repositoryClass=CategoryBrandSellerProductOptionRepository::class)
 *
 * @UniqueEntity(fields={"category", "brand", "productOption"})
 */
class CategoryBrandSellerProductOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"category_brand_seller_product_option.index", "category_brand_seller_product_option.show"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Groups({"category_brand_seller_product_option.index", "category_brand_seller_product_option.show"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class)
     *
     * @Assert\NotBlank()
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category_brand_seller_product_option.index", "category_brand_seller_product_option.show"})
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOption::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Groups({"category_brand_seller_product_option.index", "category_brand_seller_product_option.show"})
     */
    private $productOption;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOptionValue::class)
     *
     * @Assert\Count(min=1)
     * @Assert\All({
     *     @Assert\Expression(
     *         "value.getOption().getCode() === this.getProductOption().getCode()",
     *         message="Only guaranties allowed!"
     *     )
     * })
     *
     * @Groups({"category_brand_seller_product_option.index", "category_brand_seller_product_option.show"})
     */
    private $values;

    /**
     * CategoryBrandSellerProductOption constructor.
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     *
     * @return CategoryBrandSellerProductOption
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Brand|null
     */
    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand|null $brand
     *
     * @return CategoryBrandSellerProductOption
     */
    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return ProductOption|null
     */
    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    /**
     * @param ProductOption $productOption
     *
     * @return CategoryBrandSellerProductOption
     */
    public function setProductOption(ProductOption $productOption): self
    {
        $this->productOption = $productOption;

        return $this;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    /**
     * @param ProductOptionValue $value
     *
     * @return $this
     */
    public function addValue(ProductOptionValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
        }

        return $this;
    }

    /**
     * @param ProductOptionValue $value
     *
     * @return $this
     */
    public function removeValue(ProductOptionValue $value): self
    {
        $this->values->removeElement($value);

        return $this;
    }
}
