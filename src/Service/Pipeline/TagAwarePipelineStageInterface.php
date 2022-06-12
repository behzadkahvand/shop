<?php

namespace App\Service\Pipeline;

interface TagAwarePipelineStageInterface extends PipelineStageInterface
{
    public static function getTag(): string;

    public static function getPriority(): int;
}
