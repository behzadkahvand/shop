<?php

namespace App\Entity;

use App\Repository\AttributeGroupRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="attribute_groups")
 * @ORM\Entity(repositoryClass=AttributeGroupRepository::class)
 *
 * @UniqueEntity(
 *     fields={"title"},
 *     message="This title already exists.",
 *     groups={"attribute.group.store", "attribute.group.update"}
 * )
 */
class AttributeGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "attribute.group.read",
     *     "category.attribute.group.read",
     *     "category.attribute.read",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"attribute.group.store"})
     * @Assert\NotNull(groups={"attribute.group.store"})
     *
     * @Groups({
     *     "attribute.group.read",
     *     "category.attribute.group.read",
     *     "category.attribute.read",
     *     "product.attribute.read",
     *     "customer.product.attribute.read"
     * })
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttributeGroup::class, mappedBy="attributeGroup", orphanRemoval=true, cascade={"persist"})
     */
    private $categoryAttributeGroups;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttribute::class, mappedBy="attributeGroup", orphanRemoval=true, cascade={"persist"})
     */
    private $categoryAttributes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isAssignedToCategory(): bool
    {
        return $this->getCategoryAttributeGroups()->count() > 0;
    }

    public function isAssignedToTemplate(): bool
    {
        return $this->getCategoryAttributes()->count() > 0;
    }

    /**
     * @return Collection|CategoryAttributeGroup[]
     */
    public function getCategoryAttributeGroups()
    {
        return $this->categoryAttributeGroups;
    }

    public function addCategoryAttributeGroup(CategoryAttributeGroup $categoryAttributeGroup): self
    {
        if (!$this->categoryAttributeGroups->contains($categoryAttributeGroup)) {
            $this->categoryAttributeGroups[] = $categoryAttributeGroup;
            $categoryAttributeGroup->setAttributeGroup($this);
        }

        return $this;
    }

    public function removeCategoryAttributeGroup(CategoryAttributeGroup $categoryAttributeGroup): self
    {
        if ($this->categoryAttributeGroups->removeElement($categoryAttributeGroup)) {
            // set the owning side to null (unless already changed)
            if ($categoryAttributeGroup->getAttributeGroup() === $this) {
                $categoryAttributeGroup->setAttributeGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CategoryAttribute[]
     */
    public function getCategoryAttributes()
    {
        return $this->categoryAttributes;
    }

    public function addCategoryAttribute(CategoryAttribute $categoryAttribute): self
    {
        if (!$this->categoryAttributes->contains($categoryAttribute)) {
            $this->categoryAttributes[] = $categoryAttribute;
            $categoryAttribute->setAttributeGroup($this);
        }

        return $this;
    }

    public function removeCategoryAttribute(CategoryAttribute $categoryAttribute): self
    {
        if ($this->categoryAttributes->removeElement($categoryAttribute)) {
            // set the owning side to null (unless already changed)
            if ($categoryAttribute->getAttributeGroup() === $this) {
                $categoryAttribute->setAttributeGroup(null);
            }
        }

        return $this;
    }
}
