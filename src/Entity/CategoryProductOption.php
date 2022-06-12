<?php

namespace App\Entity;

use App\Repository\CategoryProductOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @Table(name="category_product_options", uniqueConstraints={
 *     @UniqueConstraint(name="category_product_option", columns={"category_id", "product_option_id"})
 * })
 * @ORM\Entity(repositoryClass=CategoryProductOptionRepository::class)
 */
class CategoryProductOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="categoryProductOptions")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     * })
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOption::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     * })
     */
    private $productOption;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOptionValue::class)
     *
     * @Groups({
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     * })
     */
    private $optionValues;

    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(?ProductOption $productOption): self
    {
        $this->productOption = $productOption;

        return $this;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(ProductOptionValue $optionValue): self
    {
        if (!$this->optionValues->contains($optionValue)) {
            $this->optionValues[] = $optionValue;
        }

        return $this;
    }

    public function removeOptionValue(ProductOptionValue $optionValue): self
    {
        $this->optionValues->removeElement($optionValue);

        return $this;
    }
}
