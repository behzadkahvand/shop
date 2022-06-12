<?php

namespace App\Messaging\Messages\Command\ElasticSearch;

class ProductBatchMessage
{
    public function __construct(private int $batchNumber, private int $limit)
    {
    }

    public function getBatchNumber(): int
    {
        return $this->batchNumber;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}