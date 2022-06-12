<?php

namespace App\Document;

use App\Dictionary\ProductImportStatusDictionary;
use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ProductImportLog
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(name="dkProductId", type="int")
     */
    protected $dkProductId;

    /**
     * @MongoDB\Field(name="dkSellerId", type="string")
     */
    protected $dkSellerId;

    /**
     * @MongoDB\Field(name="sellerId", type="int")
     */
    protected $sellerId;

    /**
     * @MongoDB\Field(name="status", type="string")
     */
    protected $status;

    /**
     * @MongoDB\Field(name="failureReason", type="string")
     */
    protected $failureReason;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getDkProductId(): ?string
    {
        return $this->dkProductId;
    }

    public function setDkProductId(string $dkProductId): self
    {
        $this->dkProductId = $dkProductId;

        return $this;
    }

    public function getDkSellerId(): ?string
    {
        return $this->dkSellerId;
    }

    public function setDkSellerId(?string $dkSellerId): self
    {
        $this->dkSellerId = $dkSellerId;

        return $this;
    }

    public function getSellerId(): ?string
    {
        return $this->sellerId;
    }

    public function setSellerId(?string $sellerId): self
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(string $failureReason): self
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function fail(?string $reason = null): void
    {
        $this->status = ProductImportStatusDictionary::FAIL;
        $this->failureReason = $reason;
        $this->updatedAt = new DateTime();
    }

    public function success(): void
    {
        $this->status = ProductImportStatusDictionary::SUCCESS;
        $this->updatedAt = new DateTime();
    }
}
