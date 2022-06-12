<?php

namespace App\Repository;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SellerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seller::class);
    }

    public function getSellerBrands(Seller $seller)
    {
        return $this->createQueryBuilder('seller')
            ->select('brand.id', 'brand.title', 'brand.code')
            ->innerJoin('seller.inventories', 'inventory')
            ->innerJoin('inventory.variant', 'productVariant')
            ->innerJoin('productVariant.product', 'product')
            ->innerJoin('product.brand', 'brand')
            ->where('seller= :seller')
            ->setParameters(compact('seller'))
            ->distinct('brand.id')
            ->getQuery()
            ->getResult();
    }

    public function getSellerCategories(Seller $seller)
    {
        return $this->createQueryBuilder('seller')
            ->select('category.id', 'category.title', 'category.code')
            ->innerJoin('seller.inventories', 'inventory')
            ->innerJoin('inventory.variant', 'productVariant')
            ->innerJoin('productVariant.product', 'product')
            ->innerJoin('product.category', 'category')
            ->where('seller= :seller')
            ->setParameters(compact('seller'))
            ->distinct('category.id')
            ->getQuery()
            ->getResult();
    }

    public function getSellerCategoriesFromInventory(Seller $seller)
    {
        return $this->createQueryBuilder('seller')
            ->select('category.id', 'category.title', 'category.code')
            ->innerJoin('seller.inventories', 'inventory')
            ->innerJoin('inventory.variant', 'productVariant')
            ->innerJoin('productVariant.product', 'product')
            ->innerJoin('product.category', 'category')
            ->where('seller= :seller')
            ->setParameters(compact('seller'))
            ->distinct('category.id')
            ->getQuery()
            ->getResult();
    }

    public function getSellerInventorySizesAndColors(Seller $seller): array
    {
        $baseQueryBuilder = $this->createQueryBuilder('seller')
                                 ->distinct(true)
                                 ->innerJoin('seller.inventories', 'inventory')
                                 ->innerJoin('inventory.variant', 'variant')
                                 ->innerJoin('variant.optionValues', 'optionValue')
                                 ->innerJoin('optionValue.option', 'option');

        $colorsQueryBuilder = clone $baseQueryBuilder;
        $colorsQueryBuilder->where('option.code = :color')
                           ->select('optionValue.value as color')
                           ->setParameter('color', 'color');

        $sizeQueryBuilder = clone $baseQueryBuilder;
        $sizeQueryBuilder->where('option.code = :size')
                           ->select('optionValue.value as size')
                           ->setParameter('size', 'size');

        $colors = array_column($colorsQueryBuilder->getQuery()->getScalarResult(), 'color');
        $sizes  = array_column($sizeQueryBuilder->getQuery()->getScalarResult(), 'size');

        return compact('colors', 'sizes');
    }

    public function findSellersWithDelayedOrder(): iterable
    {
        $qb = $this->createQueryBuilder('seller')
                   ->distinct(true)
                   ->innerJoin('seller.orderItems', 'order_item')
                   ->where('order_item.status = :waiting_for_send')
                   ->andWhere('order_item.sendDate IS NOT NULL AND order_item.sendDate < :now')
                   ->setParameters([
                       'now'              => date('Y-m-d H:i:s'),
                       'waiting_for_send' => SellerOrderItemStatus::WAITING_FOR_SEND,
                   ]);

        $i = 0;
        foreach ($qb->getQuery()->toIterable() as $seller) {
            $i++;

            yield $seller;

            if ($i === 100) {
                $this->_em->clear(Seller::class);
                $i = 0;
            }
        }

        $this->_em->clear(Seller::class);
    }

    /**
     * @return Seller[]
     */
    public function getByIdsAsAssociatedArray(array $sellerIds): array
    {
        return $this->createQueryBuilder('seller', 'seller.id')
                    ->select('seller')
                    ->where('seller.id IN (:sellerIds)')
                    ->setParameter('sellerIds', $sellerIds)
                    ->getQuery()
                    ->getResult();
    }

    public function getBestSellers(int $maxResults): array
    {
        return $this->createQueryBuilder('seller')
                    ->select('seller')
                    ->leftJoin('seller.score', 'score')
                    ->orderBy('score.totalScore', 'DESC')
                    ->setMaxResults($maxResults)
                    ->getQuery()
                    ->getResult();
    }
}
