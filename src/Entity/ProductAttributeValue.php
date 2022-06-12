<?php

namespace App\Entity;

abstract class ProductAttributeValue
{
    abstract public function getId(): ?int;

    abstract public function setProductAttribute(ProductAttribute $productAttribute): ProductAttributeValue;

    abstract public function getProductAttribute(): ?ProductAttribute;
}
