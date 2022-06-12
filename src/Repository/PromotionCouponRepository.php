<?php

namespace App\Repository;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromotionCoupon|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionCoupon|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionCoupon[]    findAll()
 * @method PromotionCoupon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionCouponRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionCoupon::class);
    }

    public function countByCodeLength(
        int $codeLength,
        ?string $prefix = null,
        ?string $suffix = null
    ): int {
        if ($prefix !== null) {
            $codeLength += strlen($prefix);
        }
        if ($suffix !== null) {
            $codeLength += strlen($suffix);
        }
        $codeTemplate = $prefix . '%' . $suffix;

        return (int) $this->createQueryBuilder('coupon')
            ->select('COUNT(coupon.id)')
            ->andWhere('LENGTH(coupon.code) = :codeLength')
            ->andWhere('coupon.code LIKE :codeTemplate')
            ->setParameter('codeLength', $codeLength)
            ->setParameter('codeTemplate', $codeTemplate)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
