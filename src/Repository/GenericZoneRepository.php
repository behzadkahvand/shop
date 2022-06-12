<?php

namespace App\Repository;

use App\Entity\GenericZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GenericZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method GenericZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method GenericZone[]    findAll()
 * @method GenericZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GenericZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GenericZone::class);
    }

    // /**
    //  * @return GenericZone[] Returns an array of GenericZone objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GenericZone
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
