<?php

namespace App\DTO\Admin;

class ShippingCategorySuggestionData
{
    protected float $length;

    protected float $width;

    protected float $height;

    protected float $weight;

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     * @return ShippingCategorySuggestionData
     */
    public function setLength(float $length): ShippingCategorySuggestionData
    {
        $this->length = $length / 1000;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return ShippingCategorySuggestionData
     */
    public function setWidth(float $width): ShippingCategorySuggestionData
    {
        $this->width = $width / 1000;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return ShippingCategorySuggestionData
     */
    public function setHeight(float $height): ShippingCategorySuggestionData
    {
        $this->height = $height / 1000;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return ShippingCategorySuggestionData
     */
    public function setWeight(float $weight): ShippingCategorySuggestionData
    {
        $this->weight = $weight / 1000;
        return $this;
    }
}
