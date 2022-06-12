<?php

namespace App\Entity;

use App\Repository\ProductAttributeNumericValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_attribute_numeric_values")
 * @ORM\Entity(repositoryClass=ProductAttributeNumericValueRepository::class)
 */
class ProductAttributeNumericValue extends ProductAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $value;

    /**
     * @ORM\OneToOne(targetEntity=ProductAttribute::class, inversedBy="numericValue", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $productAttribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
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
