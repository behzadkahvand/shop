<?php

namespace App\Repository;

use App\Dictionary\InventoryStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends ServiceEntityRepository
{
    private int $resultCacheExpireTime;

    public function __construct(ManagerRegistry $registry, int $resultCacheExpireTime)
    {
        parent::__construct($registry, Inventory::class);

        $this->resultCacheExpireTime = $resultCacheExpireTime;
    }

    public function getFindAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('inventory')->orderBy('inventory.id', 'ASC');
    }

    public function getCountBySellerAndStatus(Seller $seller, bool $isActive = true): int
    {
        $cacheKey = 'count' . ($isActive ? 'A' : 'Ina') . 'ctiveInventories-' . $seller->getId();
        $result   = $this->createQueryBuilder('inventory')
                         ->select('count(inventory.id) as count')
                         ->where('inventory.seller = :seller')
                         ->andWhere('inventory.isActive = :status')
                         ->setParameters([
                             'seller' => $seller,
                             'status' => (int) $isActive,
                         ])
                         ->getQuery()
                         ->enableResultCache($this->resultCacheExpireTime, $cacheKey)
                         ->getResult();

        return (int) $result[0]['count'];
    }

    public function getCountActiveWithoutStockBySeller(Seller $seller): int
    {
        $result = $this->createQueryBuilder('inventory')
                       ->select('count(inventory.id) as count')
                       ->where('inventory.seller = :seller')
                       ->andWhere('inventory.isActive = :active')
                       ->andWhere('inventory.sellerStock = :noStock')
                       ->setParameters([
                           'seller'    => $seller,
                           'active'    => 1,
                           'noStock'   => 0,
                       ])
                       ->getQuery()
                       ->enableResultCache(
                           $this->resultCacheExpireTime,
                           'countActiveInventoriesWithoutStock-' . $seller->getId()
                       )
                       ->getResult();

        return (int) $result[0]['count'];
    }

    public function getAvailableInventoriesByProductId(int $productId)
    {
        return $this->createQueryBuilder('Inventory')
                    ->select('Partial Inventory.{id, finalPrice}')
                    ->innerJoin('Inventory.variant', 'ProductVariant')
                    ->where('Inventory.isActive = 1')
                    ->andWhere('Inventory.status = :confirmed')
                    ->andWhere('Inventory.sellerStock > 0')
                    ->andWhere('IDENTITY(ProductVariant.product) = :productId')
                    ->setParameters([
                        'confirmed' => InventoryStatus::CONFIRMED,
                        'productId' => $productId,
                    ])
                    ->getQuery()
                    ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
                    ->getResult();
    }

    public function iteratorForSeller(Seller $seller)
    {
        $qb = $this->createQueryBuilder('inventory');

        return $qb
            ->join('inventory.variant', 'variant')
            ->join('variant.product', 'product')
            ->join('product.category', 'category')
            ->andWhere($qb->expr()->eq('inventory.seller', ':seller'))
            ->setParameter('seller', $seller)
            ->getQuery()
            ->toIterable();
    }

    public function findBySellerCodeAndInventoryIds(string $sellerCode, array $inventoryIds)
    {
        $qb = $this->createQueryBuilder('inventory');
        $qb->join('inventory.seller', 'seller');
        $qb->andWhere($qb->expr()->eq('seller.identifier', ':sellerCode'))
           ->setParameter('sellerCode', $sellerCode);
        $qb->andWhere($qb->expr()->in('inventory.id', ':inventoryIds'))
           ->setParameter('inventoryIds', $inventoryIds);

        $inventories = $qb->getQuery()->getResult();

        return array_reduce($inventories, function ($carry, Inventory $inventory) {
            $carry[$inventory->getId()] = $inventory;

            return $carry;
        }, []);
    }

    public function findDepotInventoryByOrder($orderId)
    {
        return $this
            ->createQueryBuilder('inventory')
            ->leftJoin('inventory.orderItems', 'order_items')
            ->innerJoin('inventory.seller', 'seller')
            ->andWhere('order_items.order = :orderId')
            ->andWhere('seller.mobile IS NOT NULL')
            ->andWhere('inventory.leadTime = 0 AND inventory.sellerStock <= 2')
            ->setParameters(['orderId' => $orderId])
            ->getQuery()
            ->getResult();
    }

    public function bestSellerProductIds(int $sellerId): array
    {
        $result = $this->createQueryBuilder('inventory')
                    ->select('product.id, product.title')
                    ->addSelect('COALESCE(sum(orderItems.quantity), 0) as itemCount')
                    ->innerJoin('inventory.variant', 'variant')
                    ->innerJoin('variant.product', 'product')
                    ->leftJoin('product.featuredImage', 'featuredImage')
                    ->leftJoin('inventory.orderItems', 'orderItems')
                    ->leftJoin('orderItems.order', 'orders')
                    ->addSelect('featuredImage.path as featuredImagePath, featuredImage.alt as featuredImageAlt')
                    ->where('inventory.seller = :seller')
                    ->andWhere('orders.status IN(:validStatuses)')
                    ->setParameters([
                        'seller'        => $sellerId,
                        'validStatuses' => [OrderStatus::CONFIRMED, OrderStatus::DELIVERED]
                    ])
                    ->groupBy('product.id')
                    ->addOrderBy('itemCount', 'DESC')
                    ->setMaxResults(10)
                    ->getQuery()
                    ->getResult();

        return array_map(function ($product) {
            $product['itemCount'] = (int)$product['itemCount'];
            return $product;
        }, $result);
    }

    public function findOneCampaignInventoryByProduct(?Product $product): ?Inventory
    {
        $result = $this->createQueryBuilder('Inventory')
                            ->innerJoin('Inventory.variant', 'ProductVariant')
                            ->where('Inventory.isActive = 1')
                            ->andWhere('Inventory.status = :confirmed')
                            ->andWhere('Inventory.hasCampaign = 1')
                            ->andWhere('Inventory.sellerStock > 0')
                            ->andWhere('ProductVariant.product = :product')
                            ->setParameters(
                                [
                                    'confirmed' => InventoryStatus::CONFIRMED,
                                    'product' => $product,

                                    ]
                            )
                            ->getQuery()
                            ->getResult();

        return count($result) ? $result[0] : null;
    }

    public function countProductCampaignInventories(Product $product): int
    {
        $result = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->innerJoin('i.variant', 'var')
            ->where('var.product = :product')
            ->andWhere('i.hasCampaign = 1')
            ->andWhere('i.sellerStock > 0')
            ->andWhere('i.isActive = 1')
            ->andWhere('i.status = :confirmed')
            ->setParameters([
                    'confirmed' => InventoryStatus::CONFIRMED,
                    'product' => $product,
                ])
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }
}
