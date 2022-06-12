<?php

namespace App\Document;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class InventoryPriceHistoryLog
{
    /**
     * @MongoDB\Id @MongoDB\Index
     */
    protected $id;

    /** @MongoDB\EmbedOne(targetDocument=InventoryPriceLog::class) */
    protected $inventory;


    /** @MongoDB\EmbedOne(targetDocument=ProductOptionValue::class) */
    protected $color;


    /** @MongoDB\EmbedOne(targetDocument=ProductOptionValue::class) */
    protected $guarantee;


    /** @MongoDB\EmbedOne(targetDocument=ProductOptionValue::class) */
    protected $size;

    /**
     * @MongoDB\Field(name="user_id",type="int")
     */
    protected $userId;

    /**
     * @MongoDB\Field(name="created_at",type="date")
     */
    protected $createdAt;


    public function getId()
    {
        return $this->id;
    }

    public function setInventory(InventoryPriceLog $inventoryLog): self
    {
        $this->inventory = $inventoryLog;
        return $this;
    }

    public function getInventory(): InventoryPriceLog
    {
        return $this->inventory;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param mixed $color
     * @return InventoryPriceHistoryLog
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
     * @return InventoryPriceHistoryLog
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
     * @return InventoryPriceHistoryLog
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
}
