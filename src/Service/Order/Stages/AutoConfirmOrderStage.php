<?php

namespace App\Service\Order\Stages;

use App\Service\Order\AutoConfirm\AutoConfirmOrderServiceInterface;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class AutoConfirmOrderStage implements TagAwarePipelineStageInterface
{
    public function __construct(private AutoConfirmOrderServiceInterface $autoConfirmOrderService)
    {
    }

    /**
     * @inheritDoc
     */
    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        $order = $payload->getOrder();

        if ($this->autoConfirmOrderService->isConfirmable($order)) {
            $this->autoConfirmOrderService->confirm($order);
        }

        return $payload;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return -35;
    }

    /**
     * @return string
     */
    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
