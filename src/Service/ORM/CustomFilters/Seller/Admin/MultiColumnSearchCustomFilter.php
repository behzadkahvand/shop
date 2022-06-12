<?php

namespace App\Service\ORM\CustomFilters\Seller\Admin;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnSearchCustomFilter
 */
final class MultiColumnSearchCustomFilter implements CustomFilterInterface
{
    private EventDispatcherInterface $dispatcher;

    /**
     * MultiColumnSearchCustomFilter constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();
        $seller      = $queryParams['filter']['seller'] ?? null;

        if (null === $seller) {
            return;
        }

        foreach (['identifier', 'name', 'nationalNumber', 'nationalCode', 'seller'] as $field) {
            if (isset($queryParams['filter'][$field])) {
                unset($queryParams['filter'][$field]);
            }
        }

        $request->query->replace($queryParams);

        if (is_array($seller)) {
            $seller = current($seller);
        }

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $seller) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $queryBuilder = $event->getQueryBuilder();

            $expression  = '%1$s.identifier = :seller OR ';
            $expression .= '%1$s.nationalNumber = :seller OR ';
            $expression .= '%1$s.nationalIdentifier = :seller OR ';
            $expression .= '%1$s.name like :seller_name';

            $queryBuilder->andWhere(sprintf($expression, $rootAlias))
                         ->setParameter('seller', $seller)
                         ->setParameter('seller_name', "%$seller%");
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
