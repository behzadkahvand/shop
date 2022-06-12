<?php

namespace App\Service\Product\Logs;

use App\Document\InventoryPriceHistoryLog;
use App\Service\MongoFilter\PipelineMongoQueryBuilder;
use Tightenco\Collect\Support\Collection;

class InventoryPriceHistoryService
{
    private PipelineMongoQueryBuilder $mongoQueryBuilder;

    public function __construct(PipelineMongoQueryBuilder $pipelineMongoQueryBuilder)
    {
        $this->mongoQueryBuilder = $pipelineMongoQueryBuilder;
    }

    public function filterPriceHistory(array $requestFilters): array
    {
        $inventoryPrices = $this->mongoQueryBuilder->filter(
            InventoryPriceHistoryLog::class,
            $requestFilters
        );

        return $this->makeResponse($inventoryPrices);
    }

    protected function makeResponse(Collection $inventoryPrices): array
    {
        /** @var InventoryPriceHistoryLog $inventoryPrice */
        $result = [];
        foreach ($inventoryPrices as $inventoryPrice) {
            $date             = $inventoryPrice->getCreatedAt()->format("Y-m-d");
            $productVariantId = $inventoryPrice->getInventory()->getProductVariantId();

            if (isset($result[$productVariantId . "_" . $date])) {
                continue;
            }

            $result[$productVariantId . "_" . $date] = [
                'priceFrom'      => $inventoryPrice->getInventory()->getPriceFrom(),
                'finalPriceFrom' => $inventoryPrice->getInventory()->getFinalPriceFrom(),
                'priceTo'        => $inventoryPrice->getInventory()->getPriceTo(),
                'finalPriceTo'   => $inventoryPrice->getInventory()->getFinalPriceTo(),
                'sellerName'     => $inventoryPrice->getInventory()->getSellerName() ?? "",
                'guarantee'      => $inventoryPrice->getGuarantee() ? $inventoryPrice->getGuarantee()->getValue() : "",
                'color'          => $inventoryPrice->getColor() ? $inventoryPrice->getColor()->getValue() : "",
                'size'           => $inventoryPrice->getSize() ? $inventoryPrice->getSize()->getValue() : "",
                'date'           => $date
            ];
        }

        return array_values($result);
    }
}
