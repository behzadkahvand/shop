<?php

namespace App\Repository;

use App\Entity\PromotionRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromotionRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionRule[]    findAll()
 * @method PromotionRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionRule::class);
    }

    // /**
    //  * @return PromotionRule[] Returns an array of PromotionRule objects
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
    public function findOneBySomeField($value): ?PromotionRule
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
