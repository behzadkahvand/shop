<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchQueryEvent;
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
        return [ProductSearchQueryEvent::class => 'onProductSearchQueryEvent'];
    }

    /**
     * @param ProductSearchQueryEvent $event
     *
     * @return void
     */
    public function onProductSearchQueryEvent(ProductSearchQueryEvent $event): void
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
            $queryBuilder->addSelect("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime, hasCampaign}");
        }

        /**
         * For calculating cashback of inventory
         *
         * @see \App\Entity\Inventory::getCashback()
         */
        $this->eagerLoadCategoryCommission($queryBuilder, $rootAlias);

        $queryBuilder->leftJoin("{$rootAlias}.featuredImage", "image")
                     ->addSelect('PARTIAL image.{id, path, alt}');

        $queryBuilder->leftJoin("{$rootAlias}.productVariants", 'ProductVariants')
                     ->addSelect('PARTIAL ProductVariants.{id}')
                     ->leftJoin('ProductVariants.inventories', 'Inventories')
                     ->addSelect('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                     ->leftJoin("ProductVariants.optionValues", "optionValues")
                     ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                     ->leftJoin("optionValues.option", "productOption")
                     ->addSelect('PARTIAL productOption.{id, code, name}');

        $queryBuilder->leftJoin("{$buyBoxAlias}.seller", "seller")
                     ->addSelect('PARTIAL seller.{id, identifier, name}');

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
