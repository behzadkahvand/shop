<?php

namespace App\Service\MongoFilter;

use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\ODM\MongoDB\Query\Builder;

class FilterPayload extends AbstractPipelinePayload
{
    private Builder $queryBuilder;
    private array $requestFiltersData = [];

    public function setQueryBuilder(Builder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }

    public function setRequestFilters(array $requestFiltersData): self
    {
        $this->requestFiltersData = $requestFiltersData;
        return $this;
    }

    public function getRequestFilters(): array
    {
        return $this->requestFiltersData;
    }
}
