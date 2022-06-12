<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class StatusFilterListener
 */
final class StatusFilterListener implements EventSubscriberInterface
{
    protected EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]];
    }

    public function onProductSearchDataEvent(SellerProductSearchDataEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN()) {
            return;
        }

        $filters = $event->getData()->getFilters();

        if (isset($filters['productVariants.inventories.finalPrice'])) {
            return;
        }

        $statuses = explode(',', is_array($filters['status']) ? $filters['status']['in'] : $filters['status']);

        $soonIndex        = array_search(ProductStatusDictionary::SOON, $statuses);
        $unavailableIndex = array_search(ProductStatusDictionary::UNAVAILABLE, $statuses);

        if ($soonIndex === false && $unavailableIndex === false) {
            return;
        }

        $unsetStatuses = [];

        if ($soonIndex !== false) {
            unset($statuses[$soonIndex]);

            $unsetStatuses[] = ProductStatusDictionary::SOON;
        }

        if ($unavailableIndex !== false) {
            unset($statuses[$unavailableIndex]);

            $unsetStatuses[] = ProductStatusDictionary::UNAVAILABLE;
        }

        unset($filters['status'], $filters['productVariants.inventories.id']);

        $data = new DoctrineSearchData(
            $filters,
            $event->getData()->getSorts(),
            $event->getData()->getCategoryCode(),
            $event->getData()->getTitle()
        );

        $event->setData($data);

        $listener = function (QueryBuilderFilterApplyingEvent $event) use (&$listener, $statuses, $unsetStatuses) {
            $this->dispatcher->removeListener(QueryBuilderFilterApplyingEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $context      = $event->getContext();
            $queryBuilder = $event->getQueryBuilder();

            if (!empty($statuses)) {
                $whereStatus = sprintf('(%1$s.status IN(:statuses) AND 0 < Inventories.id)', $rootAlias);

                $queryBuilder->setParameter('statuses', array_values($statuses));
            } else {
                $whereStatus = '';
            }

            $whereStatus .= !empty($statuses) && !empty($unsetStatuses) ? ' OR ' : '';

            if (!empty($unsetStatuses)) {
                $whereStatus .= sprintf('(%1$s.status IN(:unsetStatuses))', $rootAlias);

                $queryBuilder->setParameter('unsetStatuses', $unsetStatuses);
            } else {
                $whereStatus .= '';
            }

            if (!empty($whereStatus)) {
                $queryBuilder->leftJoin("{$rootAlias}.productVariants", 'ProductVariants')
                             ->addSelect('PARTIAL ProductVariants.{id}')
                             ->leftJoin('ProductVariants.inventories', 'Inventories')
                             ->addSelect('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                             ->andWhere($whereStatus);

                $context->setAlias(Product::class, ProductVariant::class, 'ProductVariants');
                $context->setAlias(ProductVariant::class, Inventory::class, 'Inventories');
            }
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
