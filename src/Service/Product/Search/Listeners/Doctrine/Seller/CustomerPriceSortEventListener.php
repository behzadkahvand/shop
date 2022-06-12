<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CustomerPriceSortEventListener implements EventSubscriberInterface
{
    protected WebsiteAreaService $websiteAreaService;

    private QueryBuilderFilterService $filterService;

    private bool $shouldApplyPriceSort = false;

    private array $priceSort;

    public function __construct(WebsiteAreaService $websiteAreaService, QueryBuilderFilterService $filterService)
    {
        $this->websiteAreaService = $websiteAreaService;
        $this->filterService      = $filterService;
    }

    public static function getSubscribedEvents()
    {
        return [
            SellerProductSearchDataEvent::class  => 'onProductSearchDataEvent',
            SellerProductSearchQueryEvent::class => ['onProductSearchQueryEvent', 100],
        ];
    }

    public function onProductSearchDataEvent(SellerProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isCustomerArea()
        ) {
            return;
        }

        $sorts     = $event->getData()->getSorts();
        $priceSort = $this->priceSort($sorts);

        if (null === $priceSort) {
            return;
        }

        $realPriceSort  = $priceSort['direction_prefix'] . $priceSort['field'];
        $priceSortIndex = array_search($realPriceSort, $sorts, true);

        unset($sorts[$priceSortIndex]);

        $data = new DoctrineSearchData(
            $event->getData()->getFilters(),
            $sorts,
            $event->getData()->getCategoryCode(),
            $event->getData()->getTitle()
        );

        $event->setData($data);

        $this->shouldApplyPriceSort = true;
        $this->priceSort            = $priceSort;
    }

    public function onProductSearchQueryEvent(SellerProductSearchQueryEvent $event): void
    {
        if (
            !$this->shouldApplyPriceSort ||
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isCustomerArea()
        ) {
            return;
        }

        $queryBuilder = $event->getQuery()->getResultQuery();
        [$rootAlias] = $queryBuilder->getRootAliases();
        $priceQuery = clone $queryBuilder;
        $pagination = $event->getPagination();

        if (null === $inventoryAlias = $this->filterService::getJoinAlias(ProductVariant::class, Inventory::class)) {
            $priceQuery->leftJoin("{$rootAlias}.productVariants", 'ProductVariants')
                       ->leftJoin('ProductVariants.inventories', 'Inventories');

            $inventoryAlias = 'Inventories';
        }

        $productIds = $priceQuery->select("{$rootAlias}.id")
                                 ->addSelect("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                                 ->addOrderBy('priceSort', $this->priceSort['direction'])
                                 ->groupBy("{$rootAlias}.id")
                                 ->setFirstResult($pagination->getOffset())
                                 ->setMaxResults($pagination->getLimit())
                                 ->getQuery()
                                 ->getScalarResult();

        $productIds = array_column($productIds, 'id');

        $pagination->setPage(1);

        if (!empty($productIds)) {
            $queryBuilder->resetDQLPart('groupBy');
            $queryBuilder->where("{$rootAlias}.id IN(:productIds)")
                         ->setParameters(['productIds' => $productIds])
                         ->orderBy(sprintf('FIELD(%s.id, \'%s\')', $rootAlias, implode("','", $productIds)));
        } else {
            $queryBuilder->resetDQLPart('orderBy')
                         ->setParameters([])
                         ->where("{$rootAlias}.id < 0");
        }

        unset($this->priceSort);

        $this->shouldApplyPriceSort = false;
    }

    /**
     * @param array $sorts
     *
     * @return array|null
     */
    protected function priceSort(array $sorts): ?array
    {
        return collect(SortParameterNormalizer::toArray($sorts))->first(
            fn($sort) => 'productVariants.inventories.finalPrice' === $sort['field']
        );
    }
}
