<?php

namespace App\Service\Order\Stages;

use App\Service\Order\Condition\OrderConditionInterface;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class CheckOrderConditionsStage implements TagAwarePipelineStageInterface
{
    protected OrderConditionInterface $condition;

    public function __construct(OrderConditionInterface $condition)
    {
        $this->condition = $condition;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $this->condition->apply($payload->getOrder());

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 1; // This stage should be the latest
    }
}
