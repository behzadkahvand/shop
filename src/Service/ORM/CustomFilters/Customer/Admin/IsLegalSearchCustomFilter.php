<?php

namespace App\Service\ORM\CustomFilters\Customer\Admin;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IsLegalSearchCustomFilter implements CustomFilterInterface
{
    protected EventDispatcherInterface $dispatcher;

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
        $isLegal     = $queryParams['filter']['isLegal'] ?? null;

        if (null === $isLegal) {
            return;
        }

        if (isset($queryParams['filter']['isLegal'])) {
            unset($queryParams['filter']['isLegal']);
        }

        $isLegal = (bool) $isLegal;

        $request->query->replace($queryParams);

        $listener = function (QueryBuilderFilterAppliedEvent $event) use (&$listener, $isLegal) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $listener);

            $queryBuilder = $event->getQueryBuilder();
            $rootAlias    = $event->getRootAlias();

            $queryBuilder->leftJoin("$rootAlias.legalAccount", 'LegalAccount');

            if ($isLegal) {
                $queryBuilder->andWhere('LegalAccount.id > 0');
            } else {
                $queryBuilder->andWhere('LegalAccount.id IS NULL');
            }
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $listener);
    }
}
