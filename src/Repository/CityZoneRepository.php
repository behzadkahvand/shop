<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\CityZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CityZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityZone[]    findAll()
 * @method CityZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CityZone::class);
    }

    public function findOneByCity(City $city): ?CityZone
    {
        return $this->createQueryBuilder('city_zone')
                    ->where(':city MEMBER OF city_zone.cities')
                    ->setParameters(compact('city'))
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
