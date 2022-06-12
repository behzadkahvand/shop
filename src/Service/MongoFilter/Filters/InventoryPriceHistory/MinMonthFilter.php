<?php

namespace App\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\AbstractPipelineFilter;
use App\Service\MongoFilter\FilterPayload;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use DateTime;

class MinMonthFilter extends AbstractPipelineFilter implements TagAwarePipelineStageInterface
{
    /**
     * @param FilterPayload $payload
     */
    protected function doInvoke(FilterPayload $payload)
    {
        $monthMin = $payload->getRequestFilters()['filter'][$this->filterName()];

        return $payload->setQueryBuilder(
            $payload->getQueryBuilder()
                ->field('created_at')
                ->gte(new DateTime("-$monthMin months"))
                ->select(["inventory.seller_name", "inventory.price_from", "inventory.price_to",
                    "inventory.final_price_from", "inventory.final_price_to", "inventory.product_variant_id",
                    "created_at", "color.value", "guarantee.value", "size.value"])
                ->sort("created_at")
                ->sort("inventory.final_price_to")
        );
    }

    public static function getPriority(): int
    {
        return 14;
    }

    public function filterName(): string
    {
        return "inventory_price.month.min";
    }
}
