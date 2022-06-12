<?php

namespace App\Repository;

use App\Entity\ShippingPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ShippingPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingPeriod::class);
    }

    public function getFindAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('s')->orderBy('s.id', 'ASC');
    }

    public function getProperPeriodGivenTime(\DateTimeInterface $specificTime)
    {
        $result = $this->createQueryBuilder('period')
                    ->where('period.start <= :time')
                    ->andWhere('period.end >= :time')
                    ->andWhere('period.isActive = :active')
                    ->setParameters([
                        'time' => $specificTime,
                        'active' => true
                    ])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult();

        return $result ? $result[0] : null;
    }
}
