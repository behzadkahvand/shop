<?php

namespace App\Service\ORM\Extension\Adapter\Sort;

use App\Service\ORM\Extension\Adapter\AbstractQueryBuilderExtension;
use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerTrait;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SortNestedFieldExtension.
 */
final class SortNestedFieldExtension extends AbstractQueryBuilderExtension
{
    use QueryJoinerTrait;

    private SortFieldExtension $sortFieldExtension;

    /**
     * SortNestedFieldExtension constructor.
     *
     * @param SortFieldExtension $sortFieldExtension
     * @param ManagerRegistry    $managerRegistry
     * @param QueryJoiner        $queryJoiner
     */
    public function __construct(
        SortFieldExtension $sortFieldExtension,
        ManagerRegistry $managerRegistry,
        QueryJoiner $queryJoiner
    ) {
        $this->sortFieldExtension = $sortFieldExtension;
        $this->queryJoiner        = $queryJoiner;

        parent::__construct($managerRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, QueryContext $context)
    {
        if (!$context->hasSort()) {
            return;
        }

        foreach (new SortParameterNormalizer($context->getSorts()) as $sortData) {
            $field = $sortData['field'];

            if (!$this->isNested($field)) {
                continue;
            }

            [$associations, $field] = $this->extractFieldFromRelations($field);

            $resource = $this->applyJoins(
                $resourceClass,
                $associations,
                $context,
                $queryBuilder,
                QueryJoiner::JOIN_TYPE_LEFT
            );

            $sorts = [
                ('DESC' === $sortData['direction'] ? '-' : '') . $field,
            ];

            $this->sortFieldExtension->applyToCollection($queryBuilder, $resource, $context->withSorts($sorts));

            $context->unsetCurrentAlias();
        }
    }
}
