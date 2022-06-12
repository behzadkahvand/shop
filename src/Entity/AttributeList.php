<?php

namespace App\Entity;

use App\Repository\AttributeListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="attribute_lists")
 * @ORM\Entity(repositoryClass=AttributeListRepository::class)
 * @UniqueEntity(
 *     fields={"title"},
 *     message="This title already exists.",
 *     groups={"attribute.list.store", "attribute.list.update"}
 * )
 */
class AttributeList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "attribute.list.read",
     *     "attribute.list.item.read",
     *     "attribute.read",
     *     "category.attribute.read",
     *     "product.attribute.read",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"attribute.list.store"})
     * @Assert\NotNull(groups={"attribute.list.store"})
     *
     * @Groups({
     *     "attribute.list.read",
     *     "attribute.list.item.read",
     *     "attribute.read",
     *     "category.attribute.read",
     *     "product.attribute.read",
     * })
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=AttributeListItem::class, mappedBy="list", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $attributeListItems;

    public function __construct()
    {
        $this->attributeListItems = new ArrayCollection();
    }

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

    /**
     * @return Collection|AttributeListItem[]
     */
    public function getAttributeListItems(): Collection
    {
        return $this->attributeListItems;
    }

    public function addAttributeListItem(AttributeListItem $attributeListItem): self
    {
        if (!$this->attributeListItems->contains($attributeListItem)) {
            $this->attributeListItems[] = $attributeListItem;
            $attributeListItem->setList($this);
        }

        return $this;
    }

    public function removeAttributeListItem(AttributeListItem $attributeListItem): self
    {
        if ($this->attributeListItems->removeElement($attributeListItem)) {
            // set the owning side to null (unless already changed)
            if ($attributeListItem->getList() === $this) {
                $attributeListItem->setList(null);
            }
        }

        return $this;
    }

    public function isAssignedToProducts(): bool
    {
        foreach ($this->getAttributeListItems() as $attributeListItem) {
            if ($attributeListItem->isAssignedToProducts()) {
                return true;
            }
        }

        return false;
    }
}
