<?php

namespace App\Repository;

use App\Entity\OrderStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderStatusLog[]    findAll()
 * @method OrderStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderStatusLog::class);
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
