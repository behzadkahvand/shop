<?php

namespace App\DTO\Admin;

use App\Entity\Product;
use App\Entity\Seller;
use Doctrine\Common\Collections\ArrayCollection;

class ProductVariantAndInventoryData
{
    protected ?string $code = null;

    protected Product $product;

    protected ArrayCollection $optionValues;

    protected Seller $seller;

    protected int $stock;

    protected int $price;

    protected int $finalPrice;

    protected int $maxPurchasePerOrder;

    protected bool $isActive = false;

    protected int $suppliesIn;

    protected ?string $sellerCode = null;

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     *
     * @return ProductVariantAndInventoryData
     */
    public function setCode(?string $code): ProductVariantAndInventoryData
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return ProductVariantAndInventoryData
     */
    public function setProduct(Product $product): ProductVariantAndInventoryData
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOptionValues(): ArrayCollection
    {
        return $this->optionValues;
    }

    /**
     * @param ArrayCollection $optionValues
     *
     * @return ProductVariantAndInventoryData
     */
    public function setOptionValues(ArrayCollection $optionValues): ProductVariantAndInventoryData
    {
        $this->optionValues = $optionValues;

        return $this;
    }

    /**
     * @return Seller
     */
    public function getSeller(): Seller
    {
        return $this->seller;
    }

    /**
     * @param Seller $seller
     *
     * @return ProductVariantAndInventoryData
     */
    public function setSeller(Seller $seller): ProductVariantAndInventoryData
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     *
     * @return ProductVariantAndInventoryData
     */
    public function setStock(int $stock): ProductVariantAndInventoryData
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return ProductVariantAndInventoryData
     */
    public function setPrice(int $price): ProductVariantAndInventoryData
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getFinalPrice(): int
    {
        return $this->finalPrice;
    }

    /**
     * @param int $finalPrice
     *
     * @return ProductVariantAndInventoryData
     */
    public function setFinalPrice(int $finalPrice): ProductVariantAndInventoryData
    {
        $this->finalPrice = $finalPrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPurchasePerOrder(): int
    {
        return $this->maxPurchasePerOrder;
    }

    /**
     * @param int $maxPurchasePerOrder
     *
     * @return ProductVariantAndInventoryData
     */
    public function setMaxPurchasePerOrder(int $maxPurchasePerOrder): ProductVariantAndInventoryData
    {
        $this->maxPurchasePerOrder = $maxPurchasePerOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getSuppliesIn(): int
    {
        return $this->suppliesIn;
    }

    /**
     * @param int $suppliesIn
     *
     * @return ProductVariantAndInventoryData
     */
    public function setSuppliesIn(int $suppliesIn): ProductVariantAndInventoryData
    {
        $this->suppliesIn = $suppliesIn;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return ProductVariantAndInventoryData
     */
    public function setIsActive(bool $isActive): ProductVariantAndInventoryData
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSellerCode(): ?string
    {
        return $this->sellerCode;
    }

    /**
     * @param string|null $sellerCode
     * @return ProductVariantAndInventoryData
     */
    public function setSellerCode(?string $sellerCode): ProductVariantAndInventoryData
    {
        $this->sellerCode = $sellerCode;
        return $this;
    }
}
