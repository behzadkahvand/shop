<?php

namespace App\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\AbstractPipelineFilter;
use App\Service\MongoFilter\FilterPayload;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use DateTime;

class MaxMonthFilter extends AbstractPipelineFilter implements TagAwarePipelineStageInterface
{
    /**
     * @param FilterPayload $payload
     */
    protected function doInvoke(FilterPayload $payload)
    {
        $monthMax = $payload->getRequestFilters()['filter'][$this->filterName()];

        return $payload->setQueryBuilder(
            $payload->getQueryBuilder()->field('created_at')
                ->lte(new DateTime("-$monthMax months"))
        );
    }

    public static function getPriority(): int
    {
        return 14;
    }

    public function filterName(): string
    {
        return "inventory_price.month.max";
    }
}
