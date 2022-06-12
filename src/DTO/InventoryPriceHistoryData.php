<?php

namespace App\DTO;

use App\Entity\ProductOptionValue;

/**
 * Class InventoryPriceHistoryData
 */
final class InventoryPriceHistoryData extends BaseDTO
{
    protected int $inventoryId;

    protected int $sellerId;

    protected string $sellerName;

    protected int $productVariantId;

    protected int $productId;

    protected string $productTitle;

    protected ?int $priceFrom;

    protected int $priceTo;

    protected ?int $finalPriceFrom;

    protected int $finalPriceTo;

    protected bool $isActive;

    protected ?int $userId;

    protected bool $isInventoryBuyBox;

    protected ?ProductOptionValue $color;

    protected ?ProductOptionValue $guarantee;

    protected ?ProductOptionValue $size;


    public function setInventoryId(int $inventoryId): self
    {
        $this->inventoryId = $inventoryId;
        return $this;
    }

    public function setSellerId(int $sellerId): self
    {
        $this->sellerId = $sellerId;
        return $this;
    }

    public function setProductVariantId(int $productVariantId): self
    {
        $this->productVariantId = $productVariantId;
        return $this;
    }

    public function setPriceFrom(?int $priceFrom): self
    {
        $this->priceFrom = $priceFrom;
        return $this;
    }

    public function setPriceTo(int $priceTo): self
    {
        $this->priceTo = $priceTo;
        return $this;
    }

    public function setFinalPriceFrom(?int $finalPriceFrom): self
    {
        $this->finalPriceFrom = $finalPriceFrom;
        return $this;
    }

    public function setFinalPriceTo(int $finalPriceTo): self
    {
        $this->finalPriceTo = $finalPriceTo;
        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getInventoryId(): int
    {
        return $this->inventoryId;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function getProductVariantId(): int
    {
        return $this->productVariantId;
    }

    public function getPriceFrom(): ?int
    {
        return $this->priceFrom;
    }

    public function getPriceTo(): int
    {
        return $this->priceTo;
    }

    public function getFinalPriceFrom(): ?int
    {
        return $this->finalPriceFrom;
    }

    public function getFinalPriceTo(): int
    {
        return $this->finalPriceTo;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param mixed $userId
     * @return InventoryPriceHistoryData
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param mixed $color
     * @return InventoryPriceHistoryData
     */
    public function setColor(?ProductOptionValue $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColor(): ?ProductOptionValue
    {
        return $this->color;
    }

    /**
     * @param mixed $guarantee
     * @return InventoryPriceHistoryData
     */
    public function setGuarantee(?ProductOptionValue $guarantee): self
    {
        $this->guarantee = $guarantee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGuarantee(): ?ProductOptionValue
    {
        return $this->guarantee;
    }

    /**
     * @param mixed $size
     * @return InventoryPriceHistoryData
     */
    public function setSize(?ProductOptionValue $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize(): ?ProductOptionValue
    {
        return $this->size;
    }


    public function setIsInventoryBuyBox(bool $isBuyBox): self
    {
        $this->isInventoryBuyBox = $isBuyBox;
        return $this;
    }


    public function getIsInventoryBuyBox(): bool
    {
        return $this->isInventoryBuyBox;
    }

    public function setSellerName(string $sellerName): self
    {
        $this->sellerName = $sellerName;
        return $this;
    }

    public function getSellerName(): string
    {
        return $this->sellerName;
    }

    public function setProductTitle(string $productTitle): self
    {
        $this->productTitle = $productTitle;
        return $this;
    }

    public function getProductTitle(): string
    {
        return $this->productTitle;
    }
}
