<?php

namespace App\Repository;

use App\Entity\OrderCancelReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderCancelReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderCancelReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderCancelReason[]    findAll()
 * @method OrderCancelReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderCancelReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderCancelReason::class);
    }

    // /**
    //  * @return OrderCancelReason[] Returns an array of OrderCancelReason objects
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
    public function findOneBySomeField($value): ?OrderCancelReason
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
