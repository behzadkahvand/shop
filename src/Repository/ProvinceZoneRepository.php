<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\CityZone;
use App\Entity\Province;
use App\Entity\ProvinceZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProvinceZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProvinceZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProvinceZone[]    findAll()
 * @method ProvinceZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProvinceZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProvinceZone::class);
    }

    public function findOneByProvince(Province $province): ?ProvinceZone
    {
        return $this->createQueryBuilder('province_zone')
                    ->where(':province MEMBER OF province_zone.provinces')
                    ->setParameters(compact('province'))
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
