<?php

namespace App\Document;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class InventoryLog
{
    /**
     * @MongoDB\Id @MongoDB\Index
     */
    protected $id;

    /**
     * @MongoDB\Field(name="inventory_id",type="int")
     */
    protected $inventoryId;

    /**
     * @MongoDB\Field(name="seller_id",type="int")
     */
    protected $sellerId;

    /**
     * @MongoDB\Field(name="variant_id",type="int")
     */
    protected $variantId;

    /**
     * @MongoDB\Field(name="status_from",type="string")
     */
    protected $statusFrom;

    /**
     * @MongoDB\Field(name="status_to",type="string")
     */
    protected $statusTo;

    /**
     * @MongoDB\Field(name="seller_stock_from",type="int")
     */
    protected $sellerStockFrom;

    /**
     * @MongoDB\Field(name="seller_stock_to",type="int")
     */
    protected $sellerStockTo;

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
     * @MongoDB\Field(name="is_active_from",type="int")
     */
    protected $isActiveFrom;

    /**
     * @MongoDB\Field(name="is_active_to",type="int")
     */
    protected $isActiveTo;

    /**
     * @MongoDB\Field(name="max_purchase_per_order_from",type="int")
     */
    protected $maxPurchasePerOrderFrom;

    /**
     * @MongoDB\Field(name="max_purchase_per_order_to",type="int")
     */
    protected $maxPurchasePerOrderTo;

    /**
     * @MongoDB\Field(name="lead_time_from",type="int")
     */
    protected $leadTimeFrom;

    /**
     * @MongoDB\Field(name="lead_time_to",type="int")
     */
    protected $leadTimeTo;

    /**
     * @MongoDB\Field(name="safe_time_from",type="int")
     */
    protected $safeTimeFrom;

    /**
     * @MongoDB\Field(name="safe_time_to",type="int")
     */
    protected $safeTimeTo;

    /**
     * @MongoDB\Field(name="updated_by",type="int")
     */
    protected $updatedBy;

    /**
     * @MongoDB\Field(name="updated_at",type="date")
     */
    protected $updatedAt;

    public function __construct(array $loggableProperties)
    {
        foreach ($loggableProperties as $loggableProperty => $value) {
            $this->$loggableProperty = $value;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getInventoryId(): int
    {
        return $this->inventoryId;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function getStatusFrom(): string
    {
        return $this->statusFrom;
    }

    public function getStatusTo(): string
    {
        return $this->statusTo;
    }

    public function getSellerStockFrom(): int
    {
        return $this->sellerStockFrom;
    }

    public function getSellerStockTo(): int
    {
        return $this->sellerStockTo;
    }

    public function getPriceFrom(): int
    {
        return $this->priceFrom;
    }

    public function getPriceTo(): int
    {
        return $this->priceTo;
    }

    public function getFinalPriceFrom(): int
    {
        return $this->finalPriceFrom;
    }

    public function getFinalPriceTo(): int
    {
        return $this->finalPriceTo;
    }

    public function getIsActiveFrom(): int
    {
        return $this->isActiveFrom;
    }

    public function getIsActiveTo(): int
    {
        return $this->isActiveTo;
    }

    public function getMaxPurchasePerOrderFrom(): int
    {
        return $this->maxPurchasePerOrderFrom;
    }

    public function getMaxPurchasePerOrderTo(): int
    {
        return $this->maxPurchasePerOrderTo;
    }

    public function getLeadTimeFrom(): int
    {
        return $this->leadTimeFrom;
    }

    public function getLeadTimeTo(): int
    {
        return $this->leadTimeTo;
    }

    public function getSafeTimeFrom(): int
    {
        return $this->safeTimeFrom;
    }

    public function getSafeTimeTo(): int
    {
        return $this->safeTimeTo;
    }

    public function getUpdatedBy(): int
    {
        return $this->updatedBy;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
