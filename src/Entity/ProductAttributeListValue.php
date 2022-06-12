<?php

namespace App\Entity;

use App\Repository\ProductAttributeListValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_attribute_list_values")
 * @ORM\Entity(repositoryClass=ProductAttributeListValueRepository::class)
 */
class ProductAttributeListValue extends ProductAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=AttributeListItem::class)
     * @ORM\JoinColumn(nullable=false, name="value")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=ProductAttribute::class, inversedBy="listValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productAttribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductAttribute(): ?ProductAttribute
    {
        return $this->productAttribute;
    }

    public function setProductAttribute(ProductAttribute $productAttribute): self
    {
        $this->productAttribute = $productAttribute;

        return $this;
    }

    public function getValue(): ?AttributeListItem
    {
        return $this->value;
    }

    public function setValue(AttributeListItem $value): self
    {
        $this->value = $value;

        return $this;
    }
}
