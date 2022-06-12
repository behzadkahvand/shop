<?php

namespace App\Entity;

use App\Repository\CategoryAttributeGroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="category_attribute_groups")
 * @ORM\Entity(repositoryClass=CategoryAttributeGroupRepository::class)
 * @UniqueEntity(
 *     fields={"category","attributeGroup"},
 *     message="The category already has assigned to this attribure group.",
 *     groups={"category.attribute.group.store", "category.attribute.group.update"}
 * )
 */
class CategoryAttributeGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"category.attribute.group.read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="categoryAttributeGroups")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category.attribute.group.read"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=AttributeGroup::class, inversedBy="categoryAttributeGroups")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"category.attribute.group.read"})
     */
    private $attributeGroup;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"category.attribute.group.read"})
     */
    private $priority = 1;

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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
