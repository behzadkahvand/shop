<?php

namespace App\Repository;

use App\Entity\AdminUserSellerOrderItemStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdminUserSellerOrderItemStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminUserSellerOrderItemStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminUserSellerOrderItemStatusLog[]    findAll()
 * @method AdminUserSellerOrderItemStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminUserSellerOrderItemStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminUserSellerOrderItemStatusLog::class);
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
