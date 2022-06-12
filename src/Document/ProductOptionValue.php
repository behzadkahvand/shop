<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\EmbeddedDocument */
class ProductOptionValue
{
    /**
     * @MongoDB\Field(name="id",type="int") @MongoDB\Index
     */
    protected $id;

    /**
     * @MongoDB\Field(name="option_id",type="int")
     */
    protected $optionId;

    /**
     * @MongoDB\Field(name="code",type="string")
     */
    protected $code;

    /**
     * @MongoDB\Field(name="value",type="string")
     */
    protected $value;

    /**
     * @MongoDB\Field(name="attributes",type="hash")
     */
    protected $attributes;

    /**
     * @param mixed $id
     * @return ProductOptionValue
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $optionId
     * @return ProductOptionValue
     */
    public function setOptionId(int $optionId): self
    {
        $this->optionId = $optionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptionId(): int
    {
        return $this->optionId;
    }

    /**
     * @param mixed $code
     * @return ProductOptionValue
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param mixed $value
     * @return ProductOptionValue
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param mixed $attributes
     * @return ProductOptionValue
     */
    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }
}
