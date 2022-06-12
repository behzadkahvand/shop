<?php

namespace App\Repository;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SellerOrderItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerOrderItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerOrderItem[]    findAll()
 * @method SellerOrderItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerOrderItemRepository extends ServiceEntityRepository
{
    private int $resultCacheExpireTime;

    public function __construct(ManagerRegistry $registry, int $resultCacheExpireTime)
    {
        parent::__construct($registry, SellerOrderItem::class);

        $this->resultCacheExpireTime = $resultCacheExpireTime;
    }

    // /**
    //  * @return SellerOrderItem[] Returns an array of SellerOrderItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SellerOrderItem
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function orderItemIsSent(OrderItem $orderItem): bool
    {
        $sellerOrderItem = $this->findOneBy(compact('orderItem'));

        if (null === $sellerOrderItem) {
            return false;
        }

        return $sellerOrderItem->isSent();
    }

    public function countOrderItems(
        Seller $seller,
        string $status,
        string $datetime = null,
        string $dateOperator = null
    ): int {
        $query = $this->createQueryBuilder('SellerOrderItem')
                      ->select('count(SellerOrderItem.id) as count')
                      ->where('SellerOrderItem.seller = :seller')
                      ->andWhere('SellerOrderItem.status = :status')
                      ->setParameters([
                          'seller' => $seller,
                          'status' => $status
                      ]);

        if ($datetime) {
            $query->andWhere('SellerOrderItem.sendDate ' . $dateOperator . ' :date')
                  ->setParameter('date', $datetime);
        }

        $result = $query->getQuery()->getResult();

        return (int)$result[0]['count'];
    }

    public function soldOrderItems(Seller $seller, string $cacheKey, string $orderCreated = null): array
    {
        $cacheKey .= '-' . $seller->getId();

        $query = $this->createQueryBuilder('SellerOrderItem')
                      ->select('count(SellerOrderItem.id) as count')
                      ->innerJoin('SellerOrderItem.orderItem', 'OrderItem')
                      ->addSelect('COALESCE(sum(OrderItem.grandTotal), 0) as total')
                      ->innerJoin('OrderItem.order', 'Orders')
                      ->where('SellerOrderItem.seller = :seller')
                      ->andWhere('Orders.status = :delivered')
                      ->setParameters([
                          'seller'    => $seller,
                          'delivered' => OrderStatus::DELIVERED
                      ]);

        if ($orderCreated) {
            $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

            $query->andWhere('Orders.createdAt <= :now')
                  ->andWhere('Orders.createdAt >= :created')
                  ->setParameter('now', $now)
                  ->setParameter('created', $orderCreated);
        }

        $result = $query->getQuery()
                        ->enableResultCache($this->resultCacheExpireTime, $cacheKey)
                        ->getResult()[0];

        return array_map(fn($value) => (int)$value, $result);
    }

    public function findByOrderAndOrderItemId(int $orderId, int $orderItemId): ?SellerOrderItem
    {
        $sellerOrderItems = $this->createQueryBuilder('seller_order_item')
                                 ->innerJoin('seller_order_item.orderItem', 'order_item')
                                 ->innerJoin('order_item.order', 'orders')
                                 ->where('orders.id = :orderId AND order_item.id = :orderItemId')
                                 ->setParameters(compact('orderId', 'orderItemId'))
                                 ->getQuery()
                                 ->getResult();

        return empty($sellerOrderItems) ? null : $sellerOrderItems[0];
    }

    /**
     * @return iterable|SellerOrderItem[]
     */
    public function findWaitingForSendItemsGroupedBySeller(): iterable
    {
        $qb = $this->createQueryBuilder('soi')
                   ->innerJoin('soi.seller', 'seller')
                   ->leftJoin('soi.orderItem', 'order_item')->addSelect('order_item')
                   ->leftJoin('order_item.orderShipment', 'order_shipment')->addSelect('order_shipment')
                   ->where('soi.status = :waiting_for_send')
                   ->andWhere('soi.sendDate IS NOT NULL')
                   ->andWhere(':now < soi.sendDate')
                   ->groupBy('seller.id')
                   ->setParameters([
                       'now'              => date('Y-m-d H:i:s'),
                       'waiting_for_send' => SellerOrderItemStatus::WAITING_FOR_SEND,
                   ]);

        $entitiesToClear = [
            SellerOrderItem::class,
            Seller::class,
            OrderItem::class,
            OrderShipment::class,
        ];

        $clearEntities = function () use ($entitiesToClear) {
            foreach ($entitiesToClear as $entity) {
                $this->_em->clear($entity);
            }
        };

        $i = 0;
        foreach ($qb->getQuery()->getResult() as $sellerOrderItem) {
            $i++;

            yield $sellerOrderItem;

            if ($i === 100) {
                $clearEntities();

                $i = 0;
            }
        }

        $clearEntities();
    }
}
