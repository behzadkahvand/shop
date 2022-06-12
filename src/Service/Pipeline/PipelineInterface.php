<?php

namespace App\Service\Pipeline;

interface PipelineInterface
{
    public function pipe(callable $stage): PipelineInterface;

    public function process(AbstractPipelinePayload $payload);

    public function __invoke(AbstractPipelinePayload $payload);
}
