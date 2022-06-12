<?php

namespace App\Repository;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderShipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderShipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderShipment[]    findAll()
 * @method OrderShipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderShipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderShipment::class);
    }

    public function getFindAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('order_shipment')->orderBy('order_shipment.id', 'ASC');
    }

    public function findShipmentForCreateTransaction(int $shipmentId): ?OrderShipment
    {
        $result = $this->createQueryBuilder('OrderShipment')
            ->select('OrderShipment')
            ->where('OrderShipment.id = :shipmentId')
            ->andWhere('OrderShipment.status IN(:validStatuses)')
            ->andWhere('OrderShipment.transaction IS NULL')
            ->setParameters([
                'shipmentId' => $shipmentId,
                'validStatuses' => [
                    OrderShipmentStatus::SENT,
                    OrderShipmentStatus::DELIVERED,
                ]
            ])
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    /**
     * @return iterable|OrderShipment[]
     */
    public function findAllWithoutDescription(): iterable
    {
        return $this->createQueryBuilder('shipment')
                          ->where('shipment.description IS NULL')
                          ->andWhere('shipment.categoryDeliveryRange IS NOT NULL')
                          ->getQuery()
                          ->toIterable();
    }

    public function getShipmentsCountThatShouldBeDeliveredAt($date): int
    {
        $statuses = [
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::CANCELED_BY_CUSTOMER,
        ];

        return (int) $this->createQueryBuilder('os')
                          ->select('COUNT(os.id)')
                          ->where('DATE(os.deliveryDate) = :date')
                          ->andWhere('os.status NOT IN (:statuses)')
                          ->setParameters(['date' => to_date_time($date)->format('Y-m-d'), 'statuses' => $statuses])
                          ->getQuery()
                          ->getSingleScalarResult();
    }

    public function findShipmentForUpdateOrderTracking(int $shipmentId)
    {
        $result = $this->createQueryBuilder('OrderShipment')
                       ->select('OrderShipment')
                       ->where('OrderShipment.id = :shipmentId')
                       ->andWhere('OrderShipment.trackingCode IS NULL OR OrderShipment.trackingCode = :empty')
                       ->andWhere('OrderShipment.period IS NULL')
                       ->setParameters([
                           'shipmentId' => $shipmentId,
                           'empty' => ""
                       ])
                       ->getQuery()
                       ->setMaxResults(1)
                       ->getResult();

        return !empty($result) ? $result[0] : null;
    }
}
