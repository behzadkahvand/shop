<?php

namespace App\Repository;

use App\Entity\PromotionAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromotionAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionAction[]    findAll()
 * @method PromotionAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionAction::class);
    }

    // /**
    //  * @return PromotionAction[] Returns an array of PromotionAction objects
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
    public function findOneBySomeField($value): ?PromotionAction
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
