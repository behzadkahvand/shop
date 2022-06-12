<?php

namespace App\Service\Order\Stages;

use App\Service\Commission\OrderCommissionChargerService;
use App\Service\Order\CreateOrderPayload;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class ChargeOrderCommissionStage implements TagAwarePipelineStageInterface
{
    public function __construct(private OrderCommissionChargerService $orderCommissionCharger)
    {
    }

    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        assert($payload instanceof CreateOrderPayload);

        $order = $payload->getOrder();
        $this->orderCommissionCharger->charge($order);

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 89;
    }
}
