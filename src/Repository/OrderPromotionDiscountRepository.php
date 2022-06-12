<?php

namespace App\Repository;

use App\Entity\OrderPromotionDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderPromotionDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderPromotionDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderPromotionDiscount[]    findAll()
 * @method OrderPromotionDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderPromotionDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderPromotionDiscount::class);
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
