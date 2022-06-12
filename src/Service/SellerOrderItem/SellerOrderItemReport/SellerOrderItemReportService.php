<?php

namespace App\Service\SellerOrderItem\SellerOrderItemReport;

use App\Entity\Inventory;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\AbstractQuery;

class SellerOrderItemReportService
{
    protected QueryBuilderFilterService $filterService;

    public function __construct(QueryBuilderFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function getQueryBuilder(array $context): AbstractQuery
    {
        $queryBuilder = $this->filterService->filter(SellerOrderItem::class, $context);

        [$rootAlias] = $queryBuilder->getRootAliases();

        $queryBuilder->select("PARTIAL {$rootAlias}.{id, status, description, sendDate}");

        if (null === $orderItemAlias = $this->filterService::getJoinAlias(SellerOrderItem::class, OrderItem::class)) {
            $queryBuilder->innerJoin("{$rootAlias}.orderItem", 'orderItem');

            $orderItemAlias = 'orderItem';
        }

        if (null === $inventoryAlias = $this->filterService::getJoinAlias(OrderItem::class, Inventory::class)) {
            $queryBuilder->innerJoin("{$orderItemAlias}.inventory", 'inventory');

            $inventoryAlias = 'inventory';
        }

        if (null === $variantAlias = $this->filterService::getJoinAlias(Inventory::class, ProductVariant::class)) {
            $queryBuilder->innerJoin("{$inventoryAlias}.variant", 'variant');

            $variantAlias = 'variant';
        }

        if (null === $productAlias = $this->filterService::getJoinAlias(ProductVariant::class, Product::class)) {
            $queryBuilder->innerJoin("{$variantAlias}.product", 'product');

            $productAlias = 'product';
        }

        if (null === $sellerAlias = $this->filterService::getJoinAlias(SellerOrderItem::class, Seller::class)) {
            $queryBuilder->innerJoin("{$rootAlias}.seller", 'sellerOrderItem');

            $sellerAlias = 'sellerOrderItem';
        }

        $queryBuilder->addSelect("PARTIAL {$sellerAlias}.{id, identifier}")
                     ->addSelect("PARTIAL {$orderItemAlias}.{id, grandTotal, quantity}")
                     ->addSelect("PARTIAL {$inventoryAlias}.{id, status, sellerStock}")
                     ->addSelect("PARTIAL {$variantAlias}.{id}")
                     ->addSelect("PARTIAL {$productAlias}.{id, title}")
                     ->innerJoin("{$orderItemAlias}.orderShipment", 'orderShipment')
                     ->addSelect('PARTIAL orderShipment.{id, deliveryDate, title}')
                     ->innerJoin("{$orderItemAlias}.order", 'orders')
                     ->addSelect('PARTIAL orders.{id, identifier, status, createdAt}')
                     ->innerJoin("{$productAlias}.category", 'category')
                     ->addSelect('PARTIAL category.{id, title}')
                     ->innerJoin("{$inventoryAlias}.seller", 'sellerInventory')
                     ->addSelect('PARTIAL sellerInventory.{id, identifier}');

        return $queryBuilder->getQuery();
    }
}
