<?php

namespace App\Entity;

use App\Repository\ProductAttributeTextValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_attribute_text_values")
 * @ORM\Entity(repositoryClass=ProductAttributeTextValueRepository::class)
 */
class ProductAttributeTextValue extends ProductAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=ProductAttribute::class, inversedBy="textValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productAttribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getProductAttribute(): ?ProductAttribute
    {
        return $this->productAttribute;
    }

    public function setProductAttribute(?ProductAttribute $productAttribute): self
    {
        $this->productAttribute = $productAttribute;

        return $this;
    }
}
