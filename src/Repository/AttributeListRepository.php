<?php

namespace App\Repository;

use App\Entity\AttributeList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AttributeList|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttributeList|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttributeList[]    findAll()
 * @method AttributeList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeList::class);
    }

    // /**
    //  * @return AttributeList[] Returns an array of AttributeList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AttributeList
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
