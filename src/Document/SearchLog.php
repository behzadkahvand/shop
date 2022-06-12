<?php

namespace App\Document;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class SearchLog
{
    /**
     * @MongoDB\Id @MongoDB\Index
     */
    protected $id;

    /**
     * @MongoDB\Field(name="term",type="string")
     */
    protected $term;

    /**
     * @MongoDB\Field(name="customer_id",type="int")
     */
    protected $customerId;

    /**
     * @MongoDB\Field(name="result_count",type="int")
     */
    protected $resultCount;

    /**
     * @MongoDB\Field(name="created_at",type="date")
     */
    protected $createdAt;


    public function getId(): int
    {
        return $this->id;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;
        return $this;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setCustomerId(?int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setResultCount(int $resultCount): self
    {
        $this->resultCount = $resultCount;
        return $this;
    }

    public function getResultCount(): int
    {
        return $this->resultCount;
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
}
