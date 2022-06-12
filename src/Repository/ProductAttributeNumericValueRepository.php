<?php

namespace App\Repository;

use App\Entity\ProductAttributeNumericValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductAttributeNumericValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttributeNumericValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttributeNumericValue[]    findAll()
 * @method ProductAttributeNumericValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductAttributeNumericValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductAttributeNumericValue::class);
    }

    // /**
    //  * @return ProductAttributeIntegerValue[] Returns an array of ProductAttributeNumericValue objects
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
    public function findOneBySomeField($value): ?ProductAttributeNumericValue
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
