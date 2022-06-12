<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
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
        return [ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]];
    }

    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN()) {
            return;
        }

        $filters = $event->getData()->getFilters();

        if (isset($filters['buyBox.finalPrice'])) {
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

        unset($filters['status'], $filters['buyBox.id']);

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
                $whereStatus = sprintf('(%1$s.status IN(:statuses) AND 0 < BuyBox.id)', $rootAlias);

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
                $queryBuilder->leftJoin("{$rootAlias}.buyBox", 'BuyBox')
                             ->addSelect("PARTIAL BuyBox.{id, price, finalPrice, leadTime}")
                             ->andWhere($whereStatus);

                $context->setAlias(Product::class, Inventory::class, 'BuyBox');
            }
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
