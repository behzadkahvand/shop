<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderItem;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class IncreaseInventoryOrderCountStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $payload->getOrder()
                ->getOrderItems()
                ->forAll(function (int $index, OrderItem $item) {
                    $item->getInventory()->incrementOrderCount();

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
}
