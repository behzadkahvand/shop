<?php

namespace App\Service\Order\Stages;

use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class RemoveCustomerCartStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $cart = $payload->getCart();
        $customer = $cart->getCustomer();
        $manager = $payload->getEntityManager();

        $customer->setCart(null);
        $cart->setCustomer(null);

        $manager->remove($cart);

        return $payload;
    }

    public static function getPriority(): int
    {
        return 68;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
