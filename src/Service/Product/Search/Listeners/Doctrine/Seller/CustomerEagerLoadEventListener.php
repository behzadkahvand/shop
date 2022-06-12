<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Queries\Doctrine\QuerySearchQuery;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EagerLoadEventListener
 */
final class CustomerEagerLoadEventListener implements EventSubscriberInterface
{
    public function __construct(
        protected QueryBuilderFilterService $filterService,
        protected WebsiteAreaService $websiteAreaService
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [SellerProductSearchQueryEvent::class => 'onProductSearchQueryEvent'];
    }

    /**
     * @param SellerProductSearchQueryEvent $event
     *
     * @return void
     */
    public function onProductSearchQueryEvent(SellerProductSearchQueryEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return;
        }

        $searchQuery  = $event->getQuery();
        $queryBuilder = $searchQuery->getDoctrineQueryBuilder();
        [$rootAlias] = $queryBuilder->getRootAliases();

        $queryBuilder->select("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, colors, status}");

        $buyBoxAlias = $this->filterService::getJoinAlias(Product::class, Inventory::class);

        if ($buyBoxAlias) {
            $queryBuilder->addSelect("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}");
        }

        /**
         * For calculating cashback of inventory
         *
         * @see \App\Entity\Inventory::getCashback()
         */
        $this->eagerLoadCategoryCommission($queryBuilder, $rootAlias);

        $queryBuilder->leftJoin("{$rootAlias}.featuredImage", "image")
                     ->addSelect('PARTIAL image.{id, path, alt}');

        $variantAlias   = $this->filterService::getJoinAlias(Product::class, ProductVariant::class);
        $inventoryAlias = $this->filterService::getJoinAlias(ProductVariant::class, Inventory::class);

        $queryBuilder->addSelect("PARTIAL {$variantAlias}.{id}")
                     ->addSelect("PARTIAL {$inventoryAlias}.{id, price, finalPrice, leadTime, isActive, status, sellerStock}")
                     ->leftJoin("{$variantAlias}.optionValues", "optionValues")
                     ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                     ->leftJoin("optionValues.option", "productOption")
                     ->addSelect('PARTIAL productOption.{id, code, name}');

        $sellerAlias = $this->filterService::getJoinAlias(Inventory::class, Seller::class);

        $queryBuilder->addSelect("PARTIAL {$sellerAlias}.{id, identifier, name}");

        $query = $queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $event->setQuery(new QuerySearchQuery($query, $searchQuery->getMeta()));

        $event->stopPropagation();
    }

    /**
     * @param $queryBuilder
     * @param $rootAlias
     */
    private function eagerLoadCategoryCommission(QueryBuilder $queryBuilder, $rootAlias): void
    {
        $categoryAlias = $this->filterService::getJoinAlias(Product::class, Category::class);

        if (null === $categoryAlias) {
            $categoryAlias = 'category';
            $queryBuilder->innerJoin("{$rootAlias}.category", $categoryAlias);
        }

        $queryBuilder->addSelect("PARTIAL {$categoryAlias}.{id, commission}");
    }
}
