<?php

namespace App\Service\ORM\CustomFilters\Inventory\Admin;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class InventoryHasDiscountCustomFilter
 */
final class InventoryHasDiscountCustomFilter implements CustomFilterInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * InventoryHasDiscountCustomFilter constructor.
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

        if (!isset($queryParams['filter']['hasDiscount'])) {
            return;
        }

        $hasDiscount = (bool) $queryParams['filter']['hasDiscount'];
        $operator    = $hasDiscount ? '!=' : '=';

        unset($queryParams['filter']['hasDiscount']);

        $request->query->replace($queryParams);

        $callback = function (QueryBuilderFilterAppliedEvent $event) use ($operator, &$callback) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $callback);

            $this->applyFilter($event, $operator);
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $callback);
    }

    /**
     * @param QueryBuilderFilterAppliedEvent $event
     * @param string                         $operator
     */
    private function applyFilter(QueryBuilderFilterAppliedEvent $event, string $operator): void
    {
        $rootAlias = $event->getRootAlias();

        $event->getQueryBuilder()
              ->andWhere("{$rootAlias}.price {$operator} {$rootAlias}.finalPrice");
    }
}
