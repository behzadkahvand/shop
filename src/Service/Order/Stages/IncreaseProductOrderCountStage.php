<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderItem;
use App\Entity\Product;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use Tightenco\Collect\Support\Collection;

final class IncreaseProductOrderCountStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $this->getProducts($payload)
             ->unique(fn(Product $product) => $product->getId())
             ->each(function (Product $product) {
                 $product->incrementOrderCount();

                 return true;
             });

        return $payload;
    }

    public static function getPriority(): int
    {
        return 40;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    private function getProducts(AbstractPipelinePayload $payload): Collection
    {
        $products = $payload->getOrder()->getOrderItems()->map(function (OrderItem $item) {
            return $item->getInventory()?->getVariant()?->getProduct();
        });

        return collect($products)->filter()->values();
    }
}
