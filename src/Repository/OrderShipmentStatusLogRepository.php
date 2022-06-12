<?php

namespace App\Repository;

use App\Entity\OrderShipmentStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderShipmentStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderShipmentStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderShipmentStatusLog[]    findAll()
 * @method OrderShipmentStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderShipmentStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderShipmentStatusLog::class);
    }

    // /**
    //  * @return OrderShipmentStatusLog[] Returns an array of OrderShipmentStatusLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderShipmentStatusLog
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
