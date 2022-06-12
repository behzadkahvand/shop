<?php

namespace App\Repository;

use App\Entity\CategoryAttributeGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryAttributeGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryAttributeGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryAttributeGroup[]    findAll()
 * @method CategoryAttributeGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryAttributeGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryAttributeGroup::class);
    }

    // /**
    //  * @return CategoryAttributeGroup[] Returns an array of CategoryAttributeGroup objects
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
    public function findOneBySomeField($value): ?CategoryAttributeGroup
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
