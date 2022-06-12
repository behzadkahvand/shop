<?php

namespace App\Entity;

use App\Repository\ProductAttributeBooleanValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_attribute_boolean_values")
 * @ORM\Entity(repositoryClass=ProductAttributeBooleanValueRepository::class)
 */
class ProductAttributeBooleanValue extends ProductAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $value;

    /**
     * @ORM\OneToOne(targetEntity=ProductAttribute::class, inversedBy="booleanValue", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $productAttribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?bool
    {
        return $this->value;
    }

    public function setValue(bool $value): self
    {
        $this->value = $value;

        return $this;
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
}
