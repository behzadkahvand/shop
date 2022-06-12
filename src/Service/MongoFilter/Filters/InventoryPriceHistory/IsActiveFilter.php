<?php

namespace App\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\AbstractPipelineFilter;
use App\Service\MongoFilter\FilterPayload;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class IsActiveFilter extends AbstractPipelineFilter implements TagAwarePipelineStageInterface
{
    /**
     * @param FilterPayload $payload
     */
    protected function doInvoke(FilterPayload $payload)
    {
         return $payload->setQueryBuilder(
             $payload->getQueryBuilder()->field('inventory.is_active')
                ->equals((bool)$payload->getRequestFilters()['filter'][$this->filterName()])
         );
    }

    public static function getPriority(): int
    {
        return 15;
    }

    public function filterName(): string
    {
        return "inventory_price.active";
    }
}
