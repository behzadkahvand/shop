<?php

namespace App\Repository;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Entity\Seller;
use App\Entity\ShippingPeriod;
use App\Service\Utils\PointService;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;

class OrderRepository extends ServiceEntityRepository
{
    protected PointService $pointService;

    public function __construct(ManagerRegistry $registry, PointService $pointService)
    {
        $this->pointService = $pointService;
        parent::__construct($registry, Order::class);
    }

    public function getFindAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('orders')->orderBy('orders.id', 'ASC');
    }

    public function findUnpaidOrderWithId(int $orderId): ?Order
    {
        $result = $this->createQueryBuilder('Orders')
            ->select('Orders')
            ->where('Orders.id = :orderId')
            ->andWhere('Orders.status IN(:statuses)')
            ->andWhere('Orders.paidAt IS NULL')
            ->setParameters([
                'orderId'  => $orderId,
                'statuses' => [
                    OrderStatus::WAIT_CUSTOMER,
                    OrderStatus::WAITING_FOR_PAY,
                ],
            ])
            ->getQuery()
            ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    public function findOrdersWithStatusAndId(int $orderId, array $statuses): ?Order
    {
        $result = $this->createQueryBuilder('Orders')
            ->select('Orders')
            ->where('Orders.id = :orderId')
            ->andWhere('Orders.status IN(:statuses)')
            ->setParameters([
                'orderId'  => $orderId,
                'statuses' => $statuses,
            ])
            ->getQuery()
            ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    public function findAllUnpaidOrdersAfterOneHour(): array
    {
        return $this->createQueryBuilder('Orders')
            ->select('Orders')
            ->andWhere('Orders.status = :status')
            ->andWhere('Orders.paidAt IS NULL')
            ->andWhere('Orders.updatedAt < :updated')
            ->setParameters([
                'status'  => OrderStatus::WAITING_FOR_PAY,
                'updated' => new DateTimeImmutable('-1 hour', new DateTimeZone('Asia/Tehran')),
            ])
            ->getQuery()
            ->getResult();
    }

    public function successCustomerOrdersByPoint(AbstractPoint $addressPoint, Customer $customer): array
    {
        return $this->createQueryBuilder('Orders')
            ->innerJoin('Orders.orderAddresses', 'orderAddress', Join::WITH, 'orderAddress.isActive = 1')
            ->where('Orders.status = :delivered')
            ->andWhere('Orders.customer = :customer')
            ->andWhere('orderAddress.coordinates = St_GeomFromText(:coordinates)')
            ->setParameters([
                'delivered'   => OrderStatus::DELIVERED,
                'customer'    => $customer,
                'coordinates' => $this->pointService->convertToDatabaseValue($addressPoint),
            ])
            ->getQuery()
            ->getResult();
    }

    public function countByCustomer(Customer $customer, $exceptIds = []): int
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.status NOT IN (:statuses)')
            ->setParameter('customer', $customer)
            ->setParameter('statuses', [OrderStatus::CANCELED, OrderStatus::CANCELED_SYSTEM]);

        if (!empty($exceptIds)) {
            $qb->andWhere($qb->expr()->notIn('o.id', ':order_ids'))
                ->setParameter('order_ids', $exceptIds);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function countByCustomerAndCoupon(
        Customer $customer,
        PromotionCoupon $coupon,
        Order $excludedOrder = null
    ): int {
        $statuses = [
            OrderStatus::CANCELED_SYSTEM,
            OrderStatus::CANCELED,
        ];

        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.promotionCoupon = :coupon')
            ->andWhere('o.status NOT IN (:statuses)')
            ->setParameter('customer', $customer)
            ->setParameter('coupon', $coupon)
            ->setParameter('statuses', $statuses);

        if ($excludedOrder) {
            $qb->andWhere($qb->expr()->neq('o.id', ':excludedOrderId'))
                ->setParameter('excludedOrderId', $excludedOrder->getId());
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function countByCoupon(PromotionCoupon $coupon, Order $excludedOrder = null): int
    {
        $statuses = [
            OrderStatus::CANCELED_SYSTEM,
            OrderStatus::CANCELED,
        ];

        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.promotionCoupon = :coupon')
            ->andWhere('o.status NOT IN (:statuses)')
            ->setParameter('coupon', $coupon)
            ->setParameter('statuses', $statuses);

        if ($excludedOrder) {
            $qb->andWhere($qb->expr()->neq('o.id', ':excludedOrderId'))
                ->setParameter('excludedOrderId', $excludedOrder->getId());
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function countByPromotion(Promotion $promotion): int
    {
        $statuses = [
            OrderStatus::CANCELED_SYSTEM,
            OrderStatus::CANCELED,
        ];

        return (int)$this->createQueryBuilder('o')
            ->join('o.promotions', 'promotion')
            ->select('COUNT(o.id)')
            ->andWhere('promotion = :promotion')
            ->andWhere('o.status NOT IN (:statuses)')
            ->setParameter('promotion', $promotion)
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCustomerLatestDeliveredOrderByProduct(Customer $customer, Product $product): ?Order
    {
        return $this->createQueryBuilder('orders')
            ->select('orders')
            ->leftJoin('orders.orderItems', 'order_items')->addSelect('order_items')
            ->leftJoin('order_items.inventory', 'inventory')->addSelect('inventory')
            ->leftJoin('inventory.variant', 'variant')->addSelect('variant')
            ->andWhere('IDENTITY(variant.product) = :productId')->setParameter('productId', $product->getId())
            ->andWhere('orders.customer = :customer')->setParameter('customer', $customer)
            ->andWhere('orders.status = :status')->setParameter('status', OrderStatus::DELIVERED)
            ->orderBy('orders.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countDeliveredOrConfirmedOrderForSeller(
        Seller $seller,
        string $datetime = null,
        string $dateOperator = null
    ): int {
        $query = $this->createQueryBuilder('o');
        $query->select('count(o.id) as count')
            ->innerJoin('o.orderItems', 'orderItem')
            ->innerJoin('orderItem.sellerOrderItem', 'sellerOrderItem')
            ->where('sellerOrderItem.seller = :seller')
            ->andWhere($query->expr()->in('o.status', ':statuses'))
            ->setParameters([
                'seller'   => $seller,
                'statuses' => [
                    OrderStatus::DELIVERED,
                    OrderStatus::CONFIRMED,
                ],
            ]);

        if ($datetime) {
            $query->andWhere('o.createdAt ' . $dateOperator . ' :date')
                ->setParameter('date', $datetime);
        }

        $result = $query->getQuery()->getResult();

        return (int)$result[0]['count'];
    }

    public function findAllWaitForPayOrdersAfterSpecificTime(DateTimeImmutable $time)
    {
        return $this->createQueryBuilder('orders')
                    ->select('orders')
                    ->andWhere('orders.status = :status')
                    ->andWhere("DATE_FORMAT(orders.updatedAt, '%Y-%m-%d %H:%i') = :updated")
                    ->setParameters([
                        'status' => OrderStatus::WAITING_FOR_PAY,
                        'updated' => $time->format('Y-m-d H:i'),
                    ])
                    ->getQuery()
                    ->getResult();
    }

    public function findWithTrackingLogs(int $identifier): ?Order
    {
        $this->_em->getFilters()->disable("softdeleteable");
        $result = $this->createQueryBuilder("orders")
                       ->select([
                           "partial orders.{id}",
                           "partial orderStatusLogs.{id,statusFrom,statusTo,createdAt}",
                           "partial orderStatusLogsUser.{id,email}",
                           "partial shipments.{id,title}",
                           "partial orderShipmentStatusLogs.{id,statusFrom,statusTo,createdAt}",
                           "partial orderShipmentStatusLogsUser.{id,email}",
                           "partial orderItems.{id, deletedAt}",
                           "partial orderItemsLogs.{id,quantityFrom,quantityTo,createdAt}",
                           "partial orderItemsLogsUser.{id,email}",
                           "partial orderItemsInventory.{id}",
                           "partial orderItemsInventoryVariant.{id}",
                           "partial orderItemsInventoryProduct.{id,title}",
                           "partial sellerOrderItem.{id,status}",
                       ])
                       ->leftJoin("orders.orderStatusLogs", "orderStatusLogs")
                       ->leftJoin("orderStatusLogs.user", "orderStatusLogsUser")
                       ->leftJoin("orders.orderItems", "orderItems")
                       ->leftJoin("orderItems.logs", "orderItemsLogs")
                       ->leftJoin("orderItemsLogs.user", "orderItemsLogsUser")
                       ->leftJoin("orders.shipments", "shipments")
                       ->leftJoin("shipments.orderShipmentStatusLogs", "orderShipmentStatusLogs")
                       ->leftJoin("orderShipmentStatusLogs.user", "orderShipmentStatusLogsUser")
                       ->leftJoin("orderItems.inventory", "orderItemsInventory")
                       ->leftJoin("orderItemsInventory.variant", "orderItemsInventoryVariant")
                       ->leftJoin("orderItemsInventoryVariant.product", "orderItemsInventoryProduct")
                       ->leftJoin("orderItems.sellerOrderItem", "sellerOrderItem")
                       ->leftJoin("sellerOrderItem.sellerOrderItemStatusLogs", "sellerOrderItemStatusLogs")
                       ->addSelect('sellerOrderItemStatusLogs')
                       ->where("orders.identifier = :identifier")
                       ->setParameter("identifier", $identifier)
                       ->getQuery()
                       ->getResult();

        return $result ? $result[0] : null;
    }

    public function getOrderIdsForBalanceAmount(): array
    {
        $result = $this->createQueryBuilder('Orders')
                       ->select('Orders.id')
                       ->where('Orders.status NOT IN(:invalidStatuses)')
                       ->setParameters(['invalidStatuses' => [OrderStatus::NEW, OrderStatus::CANCELED_SYSTEM]])
                       ->getQuery()
                       ->getResult();

        return array_column($result, 'id');
    }

    public function getExpressOrdersWithDelayInShipmentOnSpecificDay(
        DateTimeInterface $deliveryDate,
        ShippingPeriod $period
    ): array {
        return $this->createQueryBuilder('Orders')
                    ->innerJoin("Orders.shipments", "Shipments")
                    ->innerJoin("Orders.customer", "Customer")
                    ->innerJoin("Orders.orderItems", "OrderItems")
                    ->Select(
                        'Orders.id AS orderId',
                        'Orders.identifier',
                        'Customer.id AS customerId',
                        'Customer.mobile',
                        'Customer.name',
                        'CONCAT(Customer.name, \' \', Customer.family) as customerFullName',
                        'SUM(OrderItems.grandTotal) as grandTotalItems'
                    )
                    ->where('Shipments.status = :waitForSupply')
                    ->andWhere('Shipments.deliveryDate = :deliveryDate')
                    ->andWhere('Shipments.period = :period')
                    ->groupBy('Orders.id')
                    ->setParameters([
                        'waitForSupply' => OrderShipmentStatus::WAITING_FOR_SUPPLY,
                        'deliveryDate'  => $deliveryDate->format('Y-m-d'),
                        'period' => $period
                    ])
                    ->getQuery()
                    ->getResult();
    }

    public function getOrdersByIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('Orders');
        $qb
            ->where($qb->expr()->in('Orders.id', ':ids'))
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
