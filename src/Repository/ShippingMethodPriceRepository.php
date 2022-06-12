<?php

namespace App\Repository;

use App\Dictionary\CityDictionary;
use App\Entity\CityZone;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethodPrice;
use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingMethodPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingMethodPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingMethodPrice[]    findAll()
 * @method ShippingMethodPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingMethodPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingMethodPrice::class);
    }

    // /**
    //  * @return ShippingMethodPrice[] Returns an array of ShippingMethodPrice objects
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

    /*
    public function findOneBySomeField($value): ?ShippingMethodPrice
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getPriceByShippingCategoryAndZone(ShippingCategory $category, Zone $zone): ShippingMethodPrice
    {
        $qb = $this->createQueryBuilder('shipping_method_price');

        $qb->innerJoin('shipping_method_price.shippingMethod', 'shipping_method')
           ->innerJoin('shipping_method.categories', 'category')
           ->where('category = :category AND shipping_method_price.zone = :zone')
           ->setParameters(compact('category', 'zone'))
           ->addSelect('shipping_method');

        return $qb->getQuery()->getSingleResult();
    }
}
