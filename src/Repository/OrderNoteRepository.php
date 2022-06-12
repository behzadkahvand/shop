<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderNote[]    findAll()
 * @method OrderNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderNote::class);
    }


    public function findByOrder(Order $order): array
    {
        return $this->createQueryBuilder('on')
            ->andWhere('on.order = :order')
            ->setParameter('order', $order)
            ->orderBy('on.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
