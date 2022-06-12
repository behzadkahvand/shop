<?php

namespace App\Service\ORM\Extension\Adapter\Search;

use App\Service\ORM\Extension\Adapter\AbstractQueryBuilderExtension;
use App\Service\ORM\Extension\Utils\OperatorAndValueExtractor;
use App\Service\ORM\Extension\Utils\QueryBuilderMethodInflector;
use App\Service\ORM\Extension\Utils\QueryBuilderMethodInvoker;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SearchFieldExtension.
 */
class SearchFieldExtension extends AbstractQueryBuilderExtension
{
    private OperatorAndValueExtractor $operatorAndValueExtractor;

    private QueryBuilderMethodInflector $methodInflector;

    private QueryBuilderMethodInvoker $methodInvoker;

    /**
     * SearchFieldExtension constructor.
     *
     * @param ManagerRegistry             $managerRegistry
     * @param OperatorAndValueExtractor   $operatorAndValueExtractor
     * @param QueryBuilderMethodInflector $methodInflector
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        OperatorAndValueExtractor $operatorAndValueExtractor,
        QueryBuilderMethodInflector $methodInflector,
        QueryBuilderMethodInvoker $methodInvoker
    ) {
        parent::__construct($managerRegistry);

        $this->operatorAndValueExtractor = $operatorAndValueExtractor;
        $this->methodInflector           = $methodInflector;
        $this->methodInvoker             = $methodInvoker;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, QueryContext $context)
    {
        if (!$context->hasFilters()) {
            return;
        }

        $filters = $context->getFilters();

        foreach ($filters as $field => $value) {
            if ($this->isNested($field) || false === $this->hasField($resourceClass, $field)) {
                continue;
            }

            foreach ($this->operatorAndValueExtractor->extract($value) as $operator => $v) {
                $alias  = sprintf('%s.%s', $context->getCurrentAlias(), $field);
                $method = $this->methodInflector->inflect($operator, $v);

                $this->methodInvoker->invoke($queryBuilder, $method, $alias, $operator, $v);
            }
        }
    }
}
