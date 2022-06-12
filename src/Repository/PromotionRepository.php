<?php

namespace App\Repository;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionProviderInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Promotion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promotion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promotion[]    findAll()
 * @method Promotion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRepository extends ServiceEntityRepository implements PromotionProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function getPromotions(PromotionSubjectInterface $subject): array
    {
        $qb = $this->createQueryBuilder('o');
        $couponAwareCondition = $qb->expr()->eq('o.couponBased', ':false');
        $qb->setParameter('false', false);

        if ($subject->getPromotionCoupon()) {
            $qb->leftJoin('o.coupons', 'coupon');
            $couponAwareCondition = $qb->expr()->orX(
                $couponAwareCondition,
                $qb->expr()->eq('coupon.id', ':couponId')
            );
            $qb->setParameter('couponId', $subject->getPromotionCoupon()->getId());
        }

        return $this->filterByActive($qb)
            ->andWhere($couponAwareCondition)
            ->addOrderBy('o.exclusive', 'desc')
            ->addOrderBy('o.priority', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActive(): array
    {
        return $this->filterByActive($this->createQueryBuilder('o'))
            ->addOrderBy('o.exclusive', 'desc')
            ->addOrderBy('o.priority', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }

    public function couponBasedQueryBuilder()
    {
        $qb = $this->createQueryBuilder('promotion');
        $qb
            ->andWhere($qb->expr()->eq('promotion.couponBased', ':true'))
            ->setParameter('true', true)
        ;

        return $qb;
    }

    protected function filterByActive(QueryBuilder $queryBuilder, ?\DateTimeInterface $date = null): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('o.enabled = :true')
            ->andWhere('o.startsAt IS NULL OR o.startsAt < :date')
            ->andWhere('o.endsAt IS NULL OR o.endsAt > :date')
            ->setParameter(':true', true)
            ->setParameter('date', $date ?: new \DateTime())
        ;
    }
}
