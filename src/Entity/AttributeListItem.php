<?php

namespace App\Entity;

use App\Repository\AttributeListItemRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="attribute_list_items")
 * @ORM\Entity(repositoryClass=AttributeListItemRepository::class)
 *
 * @UniqueEntity(
 *     fields={"title", "list"},
 *     message="This item already exists in the list!",
 *     groups={"attribute.list.item.store", "attribute.list.item.update"}
 * )
 */
class AttributeListItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"attribute.list.item.read", "product.attribute.read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"attribute.list.item.store"})
     * @Assert\NotNull(groups={"attribute.list.item.store"})
     *
     * @Groups({"attribute.list.item.read", "product.attribute.read"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=AttributeList::class, inversedBy="attributeListItems")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"attribute.list.item.read"})
     */
    private $list;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeListValue::class, mappedBy="value", orphanRemoval=true)
     */
    private $productAttributeListValues;

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

    public function getList(): ?AttributeList
    {
        return $this->list;
    }

    public function setList(?AttributeList $list): self
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return Collection|ProductAttributeListValue[]
     */
    public function getProductAttributeListValue(): Collection
    {
        return $this->productAttributeListValues;
    }

    public function addProductAttributeListValue(ProductAttributeListValue $productAttributeListValue): self
    {
        if (!$this->productAttributeListValues->contains($productAttributeListValue)) {
            $this->productAttributeListValues[] = $productAttributeListValue;
            $productAttributeListValue->setValue($this);
        }

        return $this;
    }

    public function removeProductAttributeListValue(ProductAttributeListValue $productAttributeListValue): self
    {
        if ($this->productAttributeListValues->contains($productAttributeListValue)) {
            $this->productAttributeListValues->removeElement($productAttributeListValue);
            // set the owning side to null (unless already changed)
            if ($productAttributeListValue->getValue() === $this) {
                $productAttributeListValue->setValue(null);
            }
        }

        return $this;
    }

    public function isAssignedToProducts(): bool
    {
        return $this->getProductAttributeListValue()->count() > 0;
    }
}
