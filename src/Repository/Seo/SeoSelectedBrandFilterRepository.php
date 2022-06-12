<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Entity\Seo\SeoSelectedFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SeoSelectedFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeoSelectedFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeoSelectedFilter[]    findAll()
 * @method SeoSelectedFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeoSelectedBrandFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoSelectedBrandFilter::class);
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

    public function findOneByCategoryAndBrand(int $categoryId, int $brandId): ?SeoSelectedBrandFilter
    {
        return $this->createQueryBuilder('SeoSelectedFilter')
                    ->where('SeoSelectedFilter.category = :categoryId')
                    ->andWhere('SeoSelectedFilter.entity = :brandId')
                    ->setParameters([
                        'categoryId' => $categoryId,
                        'brandId'    => $brandId,
                    ])
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
