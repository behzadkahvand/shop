<?php

namespace App\Entity;

use App\Repository\CategoryAttributeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="category_attributes")
 * @ORM\Entity(repositoryClass=CategoryAttributeRepository::class)
 * @UniqueEntity(
 *     fields={"category","attributeGroup", "attribute"},
 *     message="The category has already assigned to this attribure and attribute group.",
 *     groups={"category.attribute.store", "category.attribute.update"}
 * )
 */
class CategoryAttribute
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"category.attribute.read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category.attribute.read"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=AttributeGroup::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category.attribute.read", "customer.product.attribute.read"})
     */
    private $attributeGroup;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class, inversedBy="categoryAttributes")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category.attribute.read", "product.attribute.read", "customer.product.attribute.read"})
     */
    private $attribute;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"category.attribute.read"})
     */
    private $priority = 1;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"category.attribute.read"})
     */
    private $isFilter = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"category.attribute.read"})
     */
    private $isRequired;

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

    public function getAttributeGroup(): ?AttributeGroup
    {
        return $this->attributeGroup;
    }

    public function setAttributeGroup(?AttributeGroup $attributeGroup): self
    {
        $this->attributeGroup = $attributeGroup;

        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getIsFilter(): ?bool
    {
        return $this->isFilter;
    }

    public function setIsFilter(bool $isFilter): self
    {
        $this->isFilter = $isFilter;

        return $this;
    }

    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }
}
