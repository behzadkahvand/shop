<?php

namespace App\Service\Product\Search;

/**
 * Class SearchResult
 */
final class SearchResult
{
    private iterable $results;

    private array $metas;

    public function __construct(iterable $results, array $metas = [])
    {
        $this->results = $results;
        $this->metas = $metas;
    }

    /**
     * @return iterable
     */
    public function getResults(): iterable
    {
        return $this->results;
    }

    /**
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }
}
