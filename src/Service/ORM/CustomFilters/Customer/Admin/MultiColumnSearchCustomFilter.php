<?php

namespace App\Service\ORM\CustomFilters\Customer\Admin;

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
        $customer    = $queryParams['filter']['customer'] ?? null;

        if (null === $customer) {
            return;
        }

        if (is_array($customer)) {
            $customer = current($customer);
        }

        foreach (['name', 'family', 'nationalNumber', 'mobile', 'email', 'customer'] as $field) {
            if (isset($queryParams['filter'][$field])) {
                unset($queryParams['filter'][$field]);
            }
        }

        $request->query->replace($queryParams);

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $customer) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $queryBuilder = $event->getQueryBuilder();
            $rootAlias    = $event->getRootAlias();

            $expression  = 'CONCAT(%1$s.name, \' \', %1$s.family) LIKE :customer_name OR ';
            $expression .= '%1$s.nationalNumber = :customer OR ';
            $expression .= '%1$s.mobile = :customer OR ';
            $expression .= '%1$s.email = :customer';

            $queryBuilder->andWhere(sprintf($expression, $rootAlias))
                         ->setParameter('customer_name', "%$customer%")
                         ->setParameter('customer', $customer);
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
