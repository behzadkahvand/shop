<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Queries\Doctrine\QuerySearchQuery;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SellerEagerLoadEventListener implements EventSubscriberInterface
{
    protected QueryBuilderFilterService $filterService;

    protected WebsiteAreaService $websiteAreaService;

    public function __construct(QueryBuilderFilterService $filterService, WebsiteAreaService $websiteAreaService)
    {
        $this->filterService      = $filterService;
        $this->websiteAreaService = $websiteAreaService;
    }

    public static function getSubscribedEvents()
    {
        return [ProductSearchQueryEvent::class => 'onProductSearchQueryEvent'];
    }

    public function onProductSearchQueryEvent(ProductSearchQueryEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_SELLER)
        ) {
            return;
        }

        $searchQuery  = $event->getQuery();
        $queryBuilder = $searchQuery->getDoctrineQueryBuilder();
        [$rootAlias] = $queryBuilder->getRootAliases();

        $queryBuilder->select("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}");

        $queryBuilder->leftJoin("{$rootAlias}.featuredImage", "image")
                     ->addSelect('PARTIAL image.{id, path, alt}');

        if (null === $buyBoxAlias = $this->filterService::getJoinAlias(Product::class, Inventory::class)) {
            $queryBuilder->leftJoin("{$rootAlias}.buyBox", "BuyBox");

            $buyBoxAlias = 'BuyBox';
        }

        $queryBuilder->addSelect("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                     ->leftJoin("{$buyBoxAlias}.seller", "seller")
                     ->addSelect('PARTIAL seller.{id, identifier, name}');

        if (null === $categoryAlias = $this->filterService::getJoinAlias(Product::class, Category::class)) {
            $queryBuilder->leftJoin("{$rootAlias}.category", "category");

            $categoryAlias = "category";
        }

        $queryBuilder->addSelect("PARTIAL {$categoryAlias}.{id}");

        if (null === $this->filterService::getJoinAlias(Product::class, ProductVariant::class)) {
            $queryBuilder->leftJoin("{$rootAlias}.productVariants", 'ProductVariants')
                         ->addSelect('PARTIAL ProductVariants.{id}')
                         ->leftJoin('ProductVariants.inventories', 'inventories')
                         ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}');
        }

        $query = $queryBuilder->getQuery();

        $event->setQuery(new QuerySearchQuery($query, $searchQuery->getMeta()));

        $event->stopPropagation();
    }
}
