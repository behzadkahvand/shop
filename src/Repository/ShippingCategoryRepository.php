<?php

namespace App\Repository;

use App\Entity\ShippingCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingCategory[]    findAll()
 * @method ShippingCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingCategory::class);
    }

    public function getShippingCategoryQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('shipping_category');
    }

    // /**
    //  * @return ShippingCategory[] Returns an array of ShippingCategory objects
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
    public function findOneBySomeField($value): ?ShippingCategory
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
