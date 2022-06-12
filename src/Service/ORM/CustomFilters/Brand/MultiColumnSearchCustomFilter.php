<?php

namespace App\Service\ORM\CustomFilters\Brand;

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
        $brand       = $queryParams['filter']['brand'] ?? null;

        if (null === $brand) {
            return;
        }

        unset($queryParams['filter']['title'], $queryParams['filter']['subtitle'], $queryParams['filter']['brand']);

        $request->query->replace($queryParams);

        if (is_array($brand)) {
            $brand = current($brand);
        }

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $brand) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $queryBuilder = $event->getQueryBuilder();

            $queryBuilder->andWhere(sprintf('%1$s.title LIKE :brand OR %1$s.subtitle LIKE :brand', $rootAlias))
                         ->setParameter('brand', "$brand%");
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
