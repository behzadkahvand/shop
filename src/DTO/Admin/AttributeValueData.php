<?php

namespace App\DTO\Admin;

use App\Entity\Attribute;

class AttributeValueData
{
    public ?Attribute $attribute = null;

    /**
     * @var mixed
     */
    public $value;

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
