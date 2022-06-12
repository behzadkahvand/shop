<?php

namespace App\Repository;

use App\Entity\SellerOrderItemStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SellerOrderItemStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerOrderItemStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerOrderItemStatusLog[]    findAll()
 * @method SellerOrderItemStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerOrderItemStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerOrderItemStatusLog::class);
    }

    // /**
    //  * @return OrderStatusLog[] Returns an array of OrderStatusLog objects
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
    public function findOneBySomeField($value): ?OrderStatusLog
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
