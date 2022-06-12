<?php

namespace App\Repository;

use App\Entity\ProductAttributeTextValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductAttributeTextValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttributeTextValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttributeTextValue[]    findAll()
 * @method ProductAttributeTextValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductAttributeTextValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductAttributeTextValue::class);
    }

    // /**
    //  * @return ProductAttributeTextValue[] Returns an array of ProductAttributeTextValue objects
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
    public function findOneBySomeField($value): ?ProductAttributeTextValue
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
