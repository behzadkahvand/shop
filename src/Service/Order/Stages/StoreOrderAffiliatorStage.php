<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderAffiliator;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class StoreOrderAffiliatorStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $utmSource = $payload->getUtmSource();
        $utmToken  = $payload->getUtmToken();

        if ($utmSource && $utmToken) {
            $order = $payload->getOrder();

            $orderAffiliator = (new OrderAffiliator())
                ->setOrder($order)
                ->setUtmSource($utmSource)
                ->setUtmToken($utmToken);

            $order->setAffiliator($orderAffiliator);
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 80;
    }
}
