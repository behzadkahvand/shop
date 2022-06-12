<?php

namespace App\Service\ORM\CustomFilters\SellerPackage\Admin;

use App\Entity\Seller;
use App\Entity\SellerPackage;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnSellerSearchCustomFilter
 */
final class MultiColumnSellerSearchCustomFilter implements CustomFilterInterface
{
    private EventDispatcherInterface $dispatcher;

    /**
     * MultiColumnSellerSearchCustomFilter constructor.
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

        $listener = function (QueryBuilderFilterApplyingEvent $event) use (&$listener, $seller) {
            $this->dispatcher->removeListener(QueryBuilderFilterApplyingEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $queryBuilder = $event->getQueryBuilder();
            $context      = $event->getContext();

            $expression  = 'Sellers.identifier = :seller OR ';
            $expression .= 'Sellers.nationalNumber = :seller OR ';
            $expression .= 'Sellers.nationalIdentifier = :seller OR ';
            $expression .= 'Sellers.name like :seller_name';

            $queryBuilder->innerJoin("{$rootAlias}.seller", 'Sellers')
                         ->addSelect('Sellers')
                         ->andWhere(sprintf($expression, $rootAlias))
                         ->setParameter('seller', $seller)
                         ->setParameter('seller_name', "%$seller%");

            $context->setAlias(SellerPackage::class, Seller::class, 'Sellers');
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
