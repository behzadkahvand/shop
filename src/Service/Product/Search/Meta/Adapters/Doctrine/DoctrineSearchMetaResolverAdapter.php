<?php

namespace App\Service\Product\Search\Meta\Adapters\Doctrine;

use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\SearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;

/**
 * Class DoctrineSearchMetaResolverAdapter
 */
final class DoctrineSearchMetaResolverAdapter implements SearchMetaResolverInterface
{
    /**
     * @var iterable|SearchMetaResolverInterface[]
     */
    private iterable $resolvers;

    public function __construct(iterable $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array
    {
        $meta = [];

        if ($data instanceof DoctrineSearchData) {
            foreach ($this->resolvers as $resolver) {
                $meta += $resolver->resolve($query, $data, $pagination);
            }
        }

        return $meta;
    }
}
