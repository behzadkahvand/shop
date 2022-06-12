<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderItem;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class DecreaseInventoryCountStockCountStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $payload->getOrder()
            ->getOrderItems()
            ->forAll(function (int $index, OrderItem $orderItem) {
                $inventory = $orderItem->getInventory();
                $quantity = $orderItem->getQuantity();

                for ($i = 1; $i <= $quantity; $i++) {
                    $inventory->decreaseStockCount();
                }

                return true;
            });

        return $payload;
    }

    public static function getPriority(): int
    {
        return -1;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
