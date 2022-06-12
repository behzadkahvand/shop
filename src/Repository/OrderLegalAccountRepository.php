<?php

namespace App\Repository;

use App\Entity\OrderLegalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderLegalAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderLegalAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderLegalAccount[]    findAll()
 * @method OrderLegalAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderLegalAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderLegalAccount::class);
    }

    // /**
    //  * @return OrderLegalAccount[] Returns an array of OrderLegalAccount objects
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
    public function findOneBySomeField($value): ?OrderLegalAccount
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
