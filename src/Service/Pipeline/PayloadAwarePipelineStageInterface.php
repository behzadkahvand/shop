<?php

namespace App\Service\Pipeline;

interface PayloadAwarePipelineStageInterface extends PipelineStageInterface
{
    public static function getSupportedPayload(): string;

    public static function getPriority(): int;
}
