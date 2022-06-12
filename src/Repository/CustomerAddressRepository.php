<?php

namespace App\Repository;

use App\Dictionary\CityDictionary;
use App\Entity\CustomerAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CustomerAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerAddress[]    findAll()
 * @method CustomerAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerAddress::class);
    }

    public function findOneInTehran(): CustomerAddress
    {
        return $this->createQueryBuilder('address')
                    ->innerJoin('address.city', 'city')
                    ->where('city.name = :name')
                    ->setParameter('name', CityDictionary::TEHRAN_NAME)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult()[0];
    }
}
