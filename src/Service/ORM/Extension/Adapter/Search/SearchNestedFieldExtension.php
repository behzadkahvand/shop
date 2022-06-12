<?php

namespace App\Service\ORM\Extension\Adapter\Search;

use App\Service\ORM\Extension\Adapter\AbstractQueryBuilderExtension;
use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerTrait;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SearchNestedFieldExtension.
 */
final class SearchNestedFieldExtension extends AbstractQueryBuilderExtension
{
    use QueryJoinerTrait;

    private SearchFieldExtension $searchFieldExtension;

    /**
     * SearchNestedFieldExtension constructor.
     *
     * @param SearchFieldExtension $searchFieldExtension
     * @param ManagerRegistry      $managerRegistry
     * @param QueryJoiner          $queryJoiner
     */
    public function __construct(
        SearchFieldExtension $searchFieldExtension,
        ManagerRegistry $managerRegistry,
        QueryJoiner $queryJoiner
    ) {
        parent::__construct($managerRegistry);

        $this->searchFieldExtension = $searchFieldExtension;
        $this->queryJoiner          = $queryJoiner;
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
            if (!$this->isNested($field)) {
                continue;
            }

            [$associations, $field] = $this->extractFieldFromRelations($field);

            $resource = $this->applyJoins(
                $resourceClass,
                $associations,
                $context,
                $queryBuilder,
                QueryJoiner::JOIN_TYPE_INNER
            );

            $this->searchFieldExtension->applyToCollection(
                $queryBuilder,
                $resource,
                $context->withFilters([$field => $value])
            );

            $context->unsetCurrentAlias();
        }
    }
}
