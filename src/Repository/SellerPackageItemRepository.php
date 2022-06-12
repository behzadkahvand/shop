<?php

namespace App\Repository;

use App\Entity\SellerPackageItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SellerPackageItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerPackageItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerPackageItem[]    findAll()
 * @method SellerPackageItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerPackageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerPackageItem::class);
    }

    // /**
    //  * @return SellerPackageItem[] Returns an array of SellerPackageItem objects
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
    public function findOneBySomeField($value): ?SellerPackageItem
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
