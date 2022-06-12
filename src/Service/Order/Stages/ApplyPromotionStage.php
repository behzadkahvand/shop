<?php

namespace App\Service\Order\Stages;

use App\Entity\Cart;
use App\Entity\Order;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use App\Service\Promotion\PromotionProcessorInterface;

class ApplyPromotionStage implements TagAwarePipelineStageInterface
{
    protected PromotionProcessorInterface $promotionProcessor;

    public function __construct(PromotionProcessorInterface $promotionProcessor)
    {
        $this->promotionProcessor = $promotionProcessor;
    }

    //@TODO Unit test!
    public function __invoke(AbstractPipelinePayload $payload)
    {
        /** @var Cart $cart */
        $cart = $payload->getCart();
        /** @var Order $order */
        $order = $payload->getOrder();
        $order->setPromotionCoupon($cart->getPromotionCoupon());

        $this->promotionProcessor->process($order);

        $order->getOrderDocument()->setAmount($order->getGrandTotal());

        return $payload;
    }

    public static function getPriority(): int
    {
        return 85;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
