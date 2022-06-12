<?php

namespace App\Repository;

use App\Entity\MarketingSellerLanding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MarketingSellerLanding|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketingSellerLanding|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketingSellerLanding[]    findAll()
 * @method MarketingSellerLanding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarketingSellerLandingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingSellerLanding::class);
    }

    // /**
    //  * @return MarketingSellerLanding[] Returns an array of MarketingSellerLanding objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MarketingSellerLanding
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
