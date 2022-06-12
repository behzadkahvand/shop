<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SeoSelectedFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SeoSelectedFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeoSelectedFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeoSelectedFilter[]    findAll()
 * @method SeoSelectedFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeoSelectedFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoSelectedFilter::class);
    }

    // /**
    //  * @return SeoSelectedFilter[] Returns an array of SeoSelectedFilter objects
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
    public function findOneBySomeField($value): ?SeoSelectedFilter
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
