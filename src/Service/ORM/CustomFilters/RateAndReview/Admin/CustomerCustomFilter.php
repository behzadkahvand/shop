<?php

namespace App\Service\ORM\CustomFilters\RateAndReview\Admin;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CustomerCustomFilter implements CustomFilterInterface
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();
        $customer    = $queryParams['filter']['customer'] ?? null;

        if (null === $customer) {
            return;
        }

        if (is_array($customer)) {
            $customer = current($customer);
        }

        unset($queryParams['filter']['customer']);

        $request->query->replace($queryParams);

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $customer) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $queryBuilder = $event->getQueryBuilder();
            $rootAlias    = $event->getRootAlias();
            $expression  = 'CONCAT(%1$s.name, \' \', %1$s.family) LIKE :customer_name';

            $queryBuilder->leftJoin("{$rootAlias}.customer", 'Customer')
                ->andWhere(sprintf($expression, 'Customer'))
                ->setParameter('customer_name', "%$customer%");
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
