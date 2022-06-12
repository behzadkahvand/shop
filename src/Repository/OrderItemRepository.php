<?php

namespace App\Repository;

use App\Entity\OrderItem;
use App\Service\Order\DeleteOrderItem\Exceptions\OrderItemNotFoundException;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function findOrFail(int $id): OrderItem
    {
        $orderItem = $this->find($id);

        if ($orderItem === null) {
            throw new OrderItemNotFoundException();
        }

        return $orderItem;
    }
}
