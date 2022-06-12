<?php

namespace App\Repository;

use App\Entity\InventoryUpdateDemand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method InventoryUpdateDemand|null find($id, $lockMode = null, $lockVersion = null)
 * @method InventoryUpdateDemand|null findOneBy(array $criteria, array $orderBy = null)
 * @method InventoryUpdateDemand[]    findAll()
 * @method InventoryUpdateDemand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryUpdateDemandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryUpdateDemand::class);
    }

    public function getRootDemandsQueryBuilder()
    {
        $qb = $this->createQueryBuilder('demand');
        $qb->andWhere($qb->expr()->isNull('demand.demand'));

        return $qb;
    }
}
