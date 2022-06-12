<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class PromotionFilterEventListener
 */
final class PromotionFilterEventListener implements EventSubscriberInterface
{
    protected WebsiteAreaService $websiteAreaService;

    protected EventDispatcherInterface $dispatcher;

    protected QueryBuilderFilterService $filterService;

    public function __construct(
        WebsiteAreaService $websiteAreaService,
        EventDispatcherInterface $dispatcher,
        QueryBuilderFilterService $filterService
    ) {
        $this->websiteAreaService = $websiteAreaService;
        $this->dispatcher         = $dispatcher;
        $this->filterService      = $filterService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [ProductSearchDataEvent::class => 'onProductSearchDataEvent'];
    }

    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isCustomerArea()
        ) {
            return;
        }

        $filters = $event->getData()->getFilters();

        if (!isset($filters['hasPromotion'])) {
            return;
        }

        if (
            isset($filters['hasPromotion'])
            && !in_array($filters['hasPromotion'], [true, 'true', 1, '1'], true)
        ) {
            throw new SearchDataValidationException('Product promotion filter is invalid!');
        }

        unset($filters['hasPromotion']);

        $data = new DoctrineSearchData(
            $filters,
            $event->getData()->getSorts(),
            $event->getData()->getCategoryCode(),
            $event->getData()->getTitle()
        );

        $event->setData($data);

        $listener = function (QueryBuilderFilterApplyingEvent $event) use (&$listener) {
            $this->dispatcher->removeListener(QueryBuilderFilterApplyingEvent::class, $listener);

            $rootAlias    = $event->getRootAlias();
            $context      = $event->getContext();
            $queryBuilder = $event->getQueryBuilder();

            if (null === $buyBoxAlias = $this->filterService::getJoinAlias(Product::class, Inventory::class)) {
                $queryBuilder->leftJoin("{$rootAlias}.buyBox", 'BuyBox')
                             ->addSelect("PARTIAL BuyBox.{id, price, finalPrice, leadTime}");

                $buyBoxAlias = 'BuyBox';

                $context->setAlias(Product::class, Inventory::class, 'BuyBox');
            }

            $queryBuilder->andWhere(sprintf('%1$s.finalPrice < %1$s.price', $buyBoxAlias));
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
