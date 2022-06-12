<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class DefaultOrderByListener
 */
final class DefaultOrderByListener implements EventSubscriberInterface
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
        return [
            ProductSearchDataEvent::class       => 'onProductSearchDataEvent',
            SellerProductSearchDataEvent::class => 'onProductSearchDataEvent',
        ];
    }

    /**
     * @param AbstractProductSearchDataEvent $event
     */
    public function onProductSearchDataEvent(AbstractProductSearchDataEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN()) {
            return;
        }

        $listener = function (QueryBuilderFilterApplyingEvent $event) use (&$listener) {
            $this->dispatcher->removeListener(QueryBuilderFilterApplyingEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $queryBuilder = $event->getQueryBuilder();
            $statuses     = [
                ProductStatusDictionary::CONFIRMED,
                ProductStatusDictionary::SOON,
                ProductStatusDictionary::UNAVAILABLE,
                ProductStatusDictionary::SHUTDOWN,
            ];

            $queryBuilder->addOrderBy(sprintf('FIELD(%s.status, \'%s\')', $rootAlias, implode("', '", $statuses)));
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
