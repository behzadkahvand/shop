<?php

namespace App\Repository;

use App\Entity\InventoryUpdateSheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InventoryUpdateSheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method InventoryUpdateSheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method InventoryUpdateSheet[]    findAll()
 * @method InventoryUpdateSheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryUpdateSheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryUpdateSheet::class);
    }

    // /**
    //  * @return InventoryUpdateSheet[] Returns an array of InventoryUpdateSheet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InventoryUpdateSheet
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
