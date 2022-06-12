<?php

namespace App\Service\ORM\Events;

use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class QueryBuilderFilterApplyingEvent
 */
final class QueryBuilderFilterApplyingEvent extends Event
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;

    /**
     * @var QueryContext
     */
    private QueryContext $context;

    /**
     * @var string
     */
    private string $rootAlias;

    /**
     * QueryBuilderFilterAppliedEvent constructor.
     *
     * @param QueryBuilder $queryBuilder
     * @param QueryContext $context
     * @param string $rootAlias
     */
    public function __construct(QueryBuilder $queryBuilder, QueryContext $context, string $rootAlias)
    {
        $this->queryBuilder = $queryBuilder;
        $this->context      = $context;
        $this->rootAlias    = $rootAlias;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return QueryContext
     */
    public function getContext(): QueryContext
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getRootAlias(): string
    {
        return $this->rootAlias;
    }
}
