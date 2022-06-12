<?php

namespace App\Repository;

use App\Entity\District;
use App\Entity\DistrictZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method DistrictZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method DistrictZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method DistrictZone[]    findAll()
 * @method DistrictZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistrictZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DistrictZone::class);
    }

    public function findOneByDistrict(District $district): ?DistrictZone
    {
        return $this->createQueryBuilder('district_zone')
                    ->where(':district MEMBER OF district_zone.districts')
                    ->setParameters(compact('district'))
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
