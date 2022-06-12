<?php

namespace App\Service\MongoFilter;

use App\Service\Pipeline\Pipeline;
use Doctrine\ODM\MongoDB\DocumentManager;
use Tightenco\Collect\Support\Collection;

class PipelineMongoQueryBuilder
{
    private DocumentManager $documentManager;

    private iterable $stages;

    public function __construct(iterable $stages, DocumentManager $documentManager)
    {
        $this->stages = $stages;
        $this->documentManager = $documentManager;
    }

    public function filter(string $sourceClass, array $requestFilters = []): Collection
    {
        $pipeline = Pipeline::fromStages($this->stages);

        $payload = (new FilterPayload())
            ->setQueryBuilder($this->documentManager->createQueryBuilder($sourceClass))
            ->setRequestFilters($requestFilters);

        /** @var FilterPayload $finalPayload */
        $finalPayload = $pipeline->process($payload);

        return $this->makeResult($finalPayload);
    }

    protected function makeResult(FilterPayload $finalPayload): Collection
    {
        return collect(
            $finalPayload
                ->getQueryBuilder()
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
