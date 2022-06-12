<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PromotionSortEventListener
 */
final class PromotionSortEventListener implements EventSubscriberInterface
{
    private array $sortData;

    protected WebsiteAreaService $websiteAreaService;

    protected QueryBuilderFilterService $filterService;

    public function __construct(
        WebsiteAreaService $websiteAreaService,
        QueryBuilderFilterService $filterService
    ) {
        $this->websiteAreaService = $websiteAreaService;
        $this->filterService      = $filterService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchDataEvent::class  => 'onProductSearchDataEvent',
            ProductSearchQueryEvent::class => ['onProductSearchQueryEvent', 2],
        ];
    }

    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isCustomerArea()
        ) {
            return;
        }

        $sorts = $event->getData()->getSorts();

        $sortData = SortParameterNormalizer::toArray($sorts);
        $fields   = array_column($sortData, 'field');

        if (false === $index = array_search('promotion', $fields, true)) {
            return;
        }

        unset($sorts[$index]);

        $data = new DoctrineSearchData(
            $event->getData()->getFilters(),
            $sorts,
            $event->getData()->getCategoryCode(),
            $event->getData()->getTitle()
        );

        $event->setData($data);

        $this->sortData = $sortData[$index];
    }

    public function onProductSearchQueryEvent(ProductSearchQueryEvent $event): void
    {
        if (
            !isset($this->sortData) ||
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isCustomerArea()
        ) {
            return;
        }

        $queryBuilder = $event->getQuery()->getResultQuery();
        [$rootAlias] = $queryBuilder->getRootAliases();

        if (null === $buyBoxAlias = $this->filterService::getJoinAlias(Product::class, Inventory::class)) {
            $buyBoxAlias = 'BuyBox';

            $queryBuilder->leftJoin("{$rootAlias}.buyBox", $buyBoxAlias)
                         ->addSelect("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}");
        }

        $queryBuilder
            ->addSelect(sprintf(
                'CASE WHEN %1$s.finalPrice < %1$s.price THEN 1 ELSE 0 END AS HIDDEN promotionSort',
                $buyBoxAlias
            ))
            ->addOrderBy('promotionSort', 'ASC' === $this->sortData['direction'] ? 'DESC' : 'ASC');

        unset($this->sortData);
    }
}
