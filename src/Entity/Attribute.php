<?php

namespace App\Entity;

use App\Dictionary\AttributeTypeDictionary;
use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="attributes")
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 *
 * @UniqueEntity(
 *     fields={"title"},
 *     message="This title already exists.",
 *     groups={"attribute.store", "attribute.update"}
 * )
 */
class Attribute
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "attribute.read",
     *     "category.attribute.read",
     *      "product.attribute.read",
     *     "product.attribute.update"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"attribute.store"})
     * @Assert\NotNull(groups={"attribute.store"})
     *
     * @Groups({
     *     "attribute.read",
     *     "category.attribute.read",
     *     "product.attribute.read",
     *     "customer.product.attribute.read",
     *     "product.attribute.update"
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"attribute.read", "category.attribute.read", "product.attribute.read"})
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"attribute.read", "category.attribute.read", "product.attribute.read"})
     */
    private $isMultiple;

    /**
     * @ORM\ManyToOne(targetEntity=AttributeList::class)
     * @ORM\JoinColumn(name="list_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @Groups({"attribute.read", "category.attribute.read", "product.attribute.read"})
     */
    private $list;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttribute::class, mappedBy="attribute", orphanRemoval=true)
     */
    private $productAttributes;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttribute::class, mappedBy="attribute", orphanRemoval=true)
     */
    private $categoryAttributes;

    public function __construct()
    {
        $this->productAttributes  = new ArrayCollection();
        $this->categoryAttributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsMultiple(): ?bool
    {
        return $this->isMultiple;
    }

    public function setIsMultiple(bool $isMultiple): self
    {
        $this->isMultiple = $isMultiple;

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

    public function isListType(): bool
    {
        return !empty($this->getList()) && $this->getType() === AttributeTypeDictionary::LIST;
    }

    public function isMultiTextType(): bool
    {
        return $this->getIsMultiple() == true && $this->getType() === AttributeTypeDictionary::TEXT;
    }

    public function isBooleanType(): bool
    {
        return $this->getType() === AttributeTypeDictionary::BOOLEAN;
    }

    public function isNumericType(): bool
    {
        return $this->getType() === AttributeTypeDictionary::NUMERIC;
    }

    public function isSimpleTextType(): bool
    {
        return $this->getIsMultiple() == false && $this->getType() === AttributeTypeDictionary::TEXT;
    }

    /**
     * @return Collection|ProductAttribute[]
     */
    public function getProductAttributes(): Collection
    {
        return $this->productAttributes;
    }

    public function addProduct(ProductAttribute $product): self
    {
        if (!$this->productAttributes->contains($product)) {
            $this->productAttributes[] = $product;
            $product->setAttribute($this);
        }

        return $this;
    }

    public function removeProduct(ProductAttribute $product): self
    {
        if ($this->productAttributes->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getAttribute() === $this) {
                $product->setAttribute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CategoryAttribute[]
     */
    public function getCategoryAttributes(): Collection
    {
        return $this->categoryAttributes;
    }

    public function addCategoryAttribute(CategoryAttribute $categoryAttribute): self
    {
        if (!$this->categoryAttributes->contains($categoryAttribute)) {
            $this->categoryAttributes[] = $categoryAttribute;
            $categoryAttribute->setAttribute($this);
        }

        return $this;
    }

    public function removeCategoryAttribute(CategoryAttribute $categoryAttribute): self
    {
        if ($this->categoryAttributes->removeElement($categoryAttribute)) {
            // set the owning side to null (unless already changed)
            if ($categoryAttribute->getAttribute() === $this) {
                $categoryAttribute->setAttribute(null);
            }
        }

        return $this;
    }

    public function isAssignedToCategory(): bool
    {
        return $this->getCategoryAttributes()->count() > 0;
    }

    public function isAssignedToProduct(): bool
    {
        return $this->getProductAttributes()->count() > 0;
    }
}
