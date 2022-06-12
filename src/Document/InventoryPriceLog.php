<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\EmbeddedDocument */
class InventoryPriceLog
{
    /**
     * @MongoDB\Field(name="id",type="int")
     */
    protected $id;

    /**
     * @MongoDB\Field(name="seller_id",type="int") @MongoDB\Index
     */
    protected $sellerId;

    /**
     * @MongoDB\Field(name="seller_name",type="string")
     */
    protected $sellerName;

    /**
     * @MongoDB\Field(name="product_variant_id",type="int")
     */
    protected $productVariantId;

    /**
     * @MongoDB\Field(name="product_id",type="int")
     */
    protected $productId;

    /**
     * @MongoDB\Field(name="product_title",type="string")
     */
    protected $productTitle;

    /**
     * @MongoDB\Field(name="price_from",type="int")
     */
    protected $priceFrom;

    /**
     * @MongoDB\Field(name="price_to",type="int")
     */
    protected $priceTo;

    /**
     * @MongoDB\Field(name="final_price_from",type="int")
     */
    protected $finalPriceFrom;

    /**
     * @MongoDB\Field(name="final_price_to",type="int")
     */
    protected $finalPriceTo;

    /**
     * @MongoDB\Field(name="is_active",type="bool")
     */
    protected $isActive;

    /**
     * @MongoDB\Field(name="is_buy_box",type="bool")
     */
    protected $isBuyBox;


    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $sellerId
     *
     * @return InventoryPriceLog
     */
    public function setSellerId(int $sellerId): self
    {
        $this->sellerId = $sellerId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    /**
     * @param mixed $productVariantId
     *
     * @return InventoryPriceLog
     */
    public function setProductVariantId(int $productVariantId): self
    {
        $this->productVariantId = $productVariantId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductVariantId(): int
    {
        return $this->productVariantId;
    }

    /**
     * @param mixed $productId
     *
     * @return InventoryPriceLog
     */
    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param mixed $priceFrom
     *
     * @return InventoryPriceLog
     */
    public function setPriceFrom(?int $priceFrom): self
    {
        $this->priceFrom = $priceFrom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceFrom(): ?int
    {
        return $this->priceFrom;
    }

    /**
     * @param mixed $priceTo
     *
     * @return InventoryPriceLog
     */
    public function setPriceTo(int $priceTo): self
    {
        $this->priceTo = $priceTo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceTo(): int
    {
        return $this->priceTo;
    }

    /**
     * @param mixed $finalPriceFrom
     *
     * @return InventoryPriceLog
     */
    public function setFinalPriceFrom(?int $finalPriceFrom): self
    {
        $this->finalPriceFrom = $finalPriceFrom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinalPriceFrom(): ?int
    {
        return $this->finalPriceFrom;
    }

    /**
     * @param mixed $finalPriceTo
     *
     * @return InventoryPriceLog
     */
    public function setFinalPriceTo(int $finalPriceTo): self
    {
        $this->finalPriceTo = $finalPriceTo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinalPriceTo(): int
    {
        return $this->finalPriceTo;
    }

    /**
     * @param mixed $isActive
     *
     * @return InventoryPriceLog
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isBuyBox
     * @return InventoryPriceLog
     */
    public function setIsBuyBox(bool $isBuyBox): self
    {
        $this->isBuyBox = $isBuyBox;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsBuyBox(): bool
    {
        return $this->isBuyBox;
    }

    public function setSellerName(string $sellerName): self
    {
        $this->sellerName = $sellerName;
        return $this;
    }

    public function getSellerName(): ?string
    {
        return $this->sellerName;
    }

    public function setProductTitle(string $productTitle): self
    {
        $this->productTitle = $productTitle;
        return $this;
    }

    public function getProductTitle(): ?string
    {
        return $this->productTitle;
    }
}
