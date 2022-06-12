<?php

namespace App\Service\ORM\Extension\Adapter\Sort;

use App\Service\ORM\Extension\Adapter\AbstractQueryBuilderExtension;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SortExtension.
 */
class SortFieldExtension extends AbstractQueryBuilderExtension
{
    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, QueryContext $context)
    {
        if (!$context->hasSort()) {
            return;
        }

        $sorts = $context->getSorts();

        foreach (new SortParameterNormalizer($sorts) as $sortData) {
            $field = $sortData['field'];

            if ($this->isNested($field) || !$this->hasField($resourceClass, $field)) {
                continue;
            }

            $alias = sprintf('%s.%s', $context->getCurrentAlias(), $field);

            $queryBuilder->addOrderBy($alias, $sortData['direction']);
        }
    }
}
