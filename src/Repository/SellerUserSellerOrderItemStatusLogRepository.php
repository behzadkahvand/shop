<?php

namespace App\Repository;

use App\Entity\SellerUserSellerOrderItemStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SellerUserSellerOrderItemStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerUserSellerOrderItemStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerUserSellerOrderItemStatusLog[]    findAll()
 * @method SellerUserSellerOrderItemStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerUserSellerOrderItemStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerUserSellerOrderItemStatusLog::class);
    }

    // /**
    //  * @return OrderPromotionDiscount[] Returns an array of OrderPromotionDiscount objects
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
    public function findOneBySomeField($value): ?OrderPromotionDiscount
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
