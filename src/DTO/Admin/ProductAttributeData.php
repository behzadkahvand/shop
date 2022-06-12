<?php

namespace App\DTO\Admin;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;

class ProductAttributeData
{
    public array $attributes;

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
