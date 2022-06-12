<?php

namespace App\Repository;

use App\Entity\OrderCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderCondition[]    findAll()
 * @method OrderCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderCondition::class);
    }

    // /**
    //  * @return OrderCondition[] Returns an array of OrderCondition objects
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
    public function findOneBySomeField($value): ?OrderCondition
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
