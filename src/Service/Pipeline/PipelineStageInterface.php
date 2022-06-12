<?php

namespace App\Service\Pipeline;

interface PipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload);
}
