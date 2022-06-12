<?php

namespace App\Repository;

use App\Dictionary\DefaultProductOptionCode;
use App\Entity\ProductOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductOption[]    findAll()
 * @method ProductOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOption::class);
    }

    // /**
    //  * @return ProductOption[] Returns an array of ProductOption objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductOption
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Returns default color and guarantee options.
     *
     * @return int|mixed|string
     */
    public function getDefaultOptions()
    {
        return $this->createQueryBuilder('po')
            ->where('po.code = :color OR po.code = :guarantee')
            ->setParameter('color', DefaultProductOptionCode::COLOR)
            ->setParameter('guarantee', DefaultProductOptionCode::GUARANTEE)
            ->getQuery()
            ->getResult();
    }
}
