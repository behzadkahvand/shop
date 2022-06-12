<?php

namespace App\Repository;

use App\Entity\PromotionDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromotionDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionDiscount[]    findAll()
 * @method PromotionDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionDiscount::class);
    }

    // /**
    //  * @return PromotionDiscount[] Returns an array of PromotionDiscount objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PromotionDiscount
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
