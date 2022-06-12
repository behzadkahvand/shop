<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seller;
use App\Entity\CampaignCommission;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CampaignCommissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignCommission::class);
    }

    /**
     * @param   Category  $category
     * @param   Brand     $brand
     * @param   Seller    $seller
     *
     * @return CampaignCommission|null
     */
    public function findActiveCommission(
        Category $category,
        Brand $brand,
        Seller $seller
    ): ?CampaignCommission {
            $result = $this
                ->createQueryBuilder('cc')
                ->where('cc.category = :category')
                ->andWhere('cc.brand = :brand')
                ->andWhere('cc.seller = :seller')
                ->andWhere('cc.startDate <= :today')
                ->andWhere('cc.endDate >= :today')
                ->andWhere('cc.terminatedAt IS NULL')
                ->setParameters(
                    [
                        'category' => $category,
                        'brand'    => $brand,
                        'seller'   => $seller,
                        'today'    => date('Y-m-d')
                    ]
                )
                ->getQuery()
                ->getOneOrNullResult();

        if (null === $result) {
            return null;
        }

        assert($result instanceof CampaignCommission);

        return $result;
    }

    public function hasActiveCommissionInGivenDatePeriod(
        Category $category,
        Brand $brand,
        Seller $seller,
        DateTime $startDate,
        DateTime $endDate
    ) {
        /**
         * This query counts commissions that their date period has intersection with the given date period
         *
         * (startDate <= givenStartDate && endDate >= givenEndDate) ||
         * ((startDate >= givenStartDate && startDate <= givenEndDate) || (endDate >= givenStartDate && endDate<= givenEndDate))
         */
        $qb = $this->createQueryBuilder('cc');

        $commissionsCount =  $qb->select('count(cc.id) as count')
        ->where('cc.category = :category')
        ->andWhere('cc.brand = :brand')
        ->andWhere('cc.seller = :seller')
        ->andWhere(
            $qb->expr()->orX(
                $qb->expr()->andX('cc.startDate <= :givenStartDate', 'cc.endDate >= :givenEndDate'),
                $qb->expr()->orX(
                    $qb->expr()->andX('cc.startDate >= :givenStartDate', 'cc.startDate <= :givenEndDate'),
                    $qb->expr()->andX('cc.endDate >= :givenStartDate', 'cc.endDate <= :givenEndDate')
                )
            )
        )
        ->andWhere('cc.terminatedAt IS NULL')
        ->setParameters(
            [
                'category' => $category,
                'brand'    => $brand,
                'seller'   => $seller,
                'givenStartDate'    => $startDate,
                'givenEndDate'    => $endDate,
            ]
        )
        ->getQuery()
        ->getSingleScalarResult();

        return $commissionsCount > 0;
    }
}
