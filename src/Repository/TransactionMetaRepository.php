<?php

namespace App\Repository;

use App\Entity\TransactionMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransactionMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionMeta[]    findAll()
 * @method TransactionMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionMetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionMeta::class);
    }

    // /**
    //  * @return TransactionMeta[] Returns an array of TransactionMeta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TransactionMeta
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
