<?php

namespace App\Repository;

use App\Entity\CouponGeneratorInstruction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CouponGeneratorInstruction|null find($id, $lockMode = null, $lockVersion = null)
 * @method CouponGeneratorInstruction|null findOneBy(array $criteria, array $orderBy = null)
 * @method CouponGeneratorInstruction[]    findAll()
 * @method CouponGeneratorInstruction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponGeneratorInstructionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponGeneratorInstruction::class);
    }

    // /**
    //  * @return PromotionCouponGeneratorInstruction[] Returns an array of PromotionCouponGeneratorInstruction objects
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
    public function findOneBySomeField($value): ?PromotionCouponGeneratorInstruction
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
