<?php

namespace App\Repository;

use App\Entity\CategoryDiscountRange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryDiscountRange|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryDiscountRange|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryDiscountRange[]    findAll()
 * @method CategoryDiscountRange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryDiscountRangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryDiscountRange::class);
    }

    // /**
    //  * @return CategoryDiscountRange[] Returns an array of CategoryDiscountRange objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryDiscountRange
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
