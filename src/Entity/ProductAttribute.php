<?php

namespace App\Entity;

use App\Repository\ProductAttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_attributes")
 * @ORM\Entity(repositoryClass=ProductAttributeRepository::class)
 * @UniqueEntity(
 *     fields={"product","attribute"},
 *     message="The category has already assigned to this attribure group.",
 *     groups={"admin.category.attribute.group.add"}
 * )
 */
class ProductAttribute
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="attributes")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class, inversedBy="productAttributes")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"customer.product.attribute.read", "product.attribute.update"})
     */
    private $attribute;

    /**
     * @ORM\OneToOne(targetEntity=ProductAttributeBooleanValue::class, mappedBy="productAttribute", cascade={"persist", "remove"})
     */
    private $booleanValue;

    /**
     * @ORM\OneToOne(targetEntity=ProductAttributeNumericValue::class, mappedBy="productAttribute", cascade={"persist", "remove"})
     */
    private $numericValue;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeTextValue::class, mappedBy="productAttribute", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $textValues;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeListValue::class, mappedBy="productAttribute", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $listValues;

    public function __construct()
    {
        $this->textValues = new ArrayCollection();
        $this->listValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getBooleanValue(): ?ProductAttributeBooleanValue
    {
        return $this->booleanValue;
    }

    public function setBooleanValue(ProductAttributeBooleanValue $booleanValue): self
    {
        // set the owning side of the relation if necessary
        if ($booleanValue->getProductAttribute() !== $this) {
            $booleanValue->setProductAttribute($this);
        }

        $this->booleanValue = $booleanValue;

        return $this;
    }

    public function getNumericValue(): ?ProductAttributeNumericValue
    {
        return $this->numericValue;
    }

    public function setNumericValue(ProductAttributeNumericValue $numericValue): self
    {
        // set the owning side of the relation if necessary
        if ($numericValue->getProductAttribute() !== $this) {
            $numericValue->setProductAttribute($this);
        }

        $this->numericValue = $numericValue;

        return $this;
    }

    /**
     * @return Collection|ProductAttributeTextValue[]
     */
    public function getTextValues(): Collection
    {
        return $this->textValues;
    }

    public function addTextValue(ProductAttributeTextValue $productAttributeTextValue): self
    {
        if (!$this->textValues->contains($productAttributeTextValue)) {
            $this->textValues[] = $productAttributeTextValue;
            $productAttributeTextValue->setProductAttribute($this);
        }

        return $this;
    }

    public function removeTextValue(ProductAttributeTextValue $productAttributeTextValue): self
    {
        if ($this->textValues->removeElement($productAttributeTextValue)) {
            // set the owning side to null (unless already changed)
            if ($productAttributeTextValue->getProductAttribute() === $this) {
                $productAttributeTextValue->setProductAttribute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductAttributeListValue[]
     */
    public function getListValues(): Collection
    {
        return $this->listValues;
    }

    public function addListValue(ProductAttributeListValue $productAttributeListValue): self
    {
        if (!$this->listValues->contains($productAttributeListValue)) {
            $this->listValues[] = $productAttributeListValue;
            $productAttributeListValue->setProductAttribute($this);
        }

        return $this;
    }

    public function removeListValue(ProductAttributeListValue $productAttributeListValue): self
    {
        if ($this->listValues->removeElement($productAttributeListValue)) {
            // set the owning side to null (unless already changed)
            if ($productAttributeListValue->getProductAttribute() === $this) {
                $productAttributeListValue->setProductAttribute(null);
            }
        }

        return $this;
    }

    public function setValue($object): self
    {
        if (is_array($object)) {
            $this->setOneToManyValue($object);
        } else {
            $this->setOneToOneValue($object);
        }

        return $this;
    }

    protected function setOneToManyValue(array $objects): void
    {
        /** @var ProductAttributeValue $object */
        foreach ($objects as $object) {
            $object->setProductAttribute($this);
            if ($this->getAttribute()->isListType()) {
                $this->addListValue($object);
            } else {
                $this->addTextValue($object);
            }
        }
    }

    protected function setOneToOneValue(ProductAttributeValue $object): void
    {
        if ($this->getAttribute()->isBooleanType()) {
            $this->setBooleanValue($object);
        } elseif ($this->getAttribute()->isNumericType()) {
            $this->setNumericValue($object);
        } else {
            $this->addTextValue($object);
        }
    }
}
