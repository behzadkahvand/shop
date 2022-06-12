<?php

namespace App\Service\ORM\CustomFilters\RateAndReview\Admin;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IsBuyerCustomFilter implements CustomFilterInterface
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();
        $isBuyer       = $queryParams['filter']['isBuyer'] ?? null;

        if (null === $isBuyer) {
            return;
        }
        $isBuyer = filter_var($isBuyer, FILTER_VALIDATE_BOOLEAN);

        unset($queryParams['filter']['isBuyer']);
        $request->query->replace($queryParams);

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $isBuyer) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $queryBuilder = $event->getQueryBuilder();

            if ($isBuyer) {
                $expression = '%1$s.order IS NOT NULL';
            } else {
                $expression = '%1$s.order IS NULL';
            }

            $queryBuilder->andWhere(sprintf($expression, $rootAlias));
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
