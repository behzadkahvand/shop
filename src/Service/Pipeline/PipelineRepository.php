<?php

namespace App\Service\Pipeline;

class PipelineRepository
{
    private array $stages;

    public function __construct(array $stages = [])
    {
        $this->stages = $stages;
    }

    public function getByPayload(string ...$payloads): PipelineInterface
    {
        $pipeline = $this->getEmptyPipeline();

        foreach ($payloads as $payload) {
            $pipeline = $this->pipe($pipeline, $this->stages['payload'][$payload] ?? []);
        }

        return $pipeline;
    }

    public function getByTag(string ...$tags): PipelineInterface
    {
        $pipeline = $this->getEmptyPipeline();

        foreach ($tags as $payload) {
            $pipeline = $this->pipe($pipeline, $this->stages['tag'][$payload] ?? []);
        }

        return $pipeline;
    }

    private function getEmptyPipeline(): PipelineInterface
    {
        return Pipeline::fromStages([]);
    }

    private function pipe(PipelineInterface $pipeline, iterable $stages): PipelineInterface
    {
        return $pipeline->pipe(Pipeline::fromStages($stages));
    }
}
