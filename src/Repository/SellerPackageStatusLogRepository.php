<?php

namespace App\Repository;

use App\Entity\SellerPackageStatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SellerPackageStatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerPackageStatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerPackageStatusLog[]    findAll()
 * @method SellerPackageStatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerPackageStatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerPackageStatusLog::class);
    }

    // /**
    //  * @return SellerPackageStatusLog[] Returns an array of SellerPackageStatusLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SellerPackageStatusLog
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
