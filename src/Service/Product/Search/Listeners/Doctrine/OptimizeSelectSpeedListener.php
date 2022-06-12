<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\AbstractProductSearchQueryEvent;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\Pagination\PaginatorUtils;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OptimizeSelectSpeedListener
 */
final class OptimizeSelectSpeedListener implements EventSubscriberInterface
{
    protected PaginatorUtils $paginatorUtils;

    public function __construct(PaginatorUtils $paginatorUtils)
    {
        $this->paginatorUtils = $paginatorUtils;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchQueryEvent::class       => ['onProductSearchQueryEvent', 1],
        ];
    }

    public function onProductSearchQueryEvent(AbstractProductSearchQueryEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN()) {
            return;
        }

        $hasPriceSort = in_array(
            'price',
            array_column(SortParameterNormalizer::toArray($event->getData()->getSorts()), 'field'),
            true
        );

        if ($hasPriceSort) {
            return;
        }

        $pagination  = $event->getPagination();
        $searchQuery = $event->getQuery();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $searchQuery->getResultQuery();
        [$rootAlias]  = $queryBuilder->getRootAliases();
        $qb           = clone $queryBuilder;

        $qb->select("PARTIAL {$rootAlias}.{id}")
           ->setFirstResult($pagination->getOffset())
           ->setMaxResults($pagination->getLimit())
           ->distinct(true);

        $entityAliases = $queryBuilder->getAllAliases();
        foreach ($queryBuilder->getDQLPart('select') as $part) {
            $select = (string)$part;
            if (!in_array($select, $entityAliases, true)) {
                $qb->addSelect($select);
            }
        }

        $q = $qb->getQuery()
                ->setHydrationMode(Query::HYDRATE_SIMPLEOBJECT);

        $productIds = array_column(
            $this->paginatorUtils->getPaginator($q)->getQuery()->getScalarResult(),
            "{$rootAlias}_id"
        );

        $pagination->setPage(1);

        if (!empty($productIds)) {
            $queryBuilder->where("{$rootAlias}.id IN (:productIds)")
                         ->setParameters(compact('productIds'))
                         ->orderBy(sprintf('FIELD(%s.id, %s)', $rootAlias, implode(',', $productIds)));
        } else {
            $queryBuilder->resetDQLPart('orderBy')
                         ->setParameters([])
                         ->where("{$rootAlias}.id < 0");
        }
    }
}
