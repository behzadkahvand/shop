<?php

namespace App\Repository;

use App\Dictionary\InventoryStatus;
use App\Entity\Cart;
use App\Service\Cart\Exceptions\CartNotFoundException;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findOrFail(string $id): Cart
    {
        $cart = $this->find($id);

        if ($cart === null) {
            throw new CartNotFoundException();
        }

        return $cart;
    }

    public function findAllAbandonedCartBySpecificTime(DateTimeImmutable $time): array
    {
        $hasAvailableItemSubQuery = $this->_em
            ->createQueryBuilder()
            ->select('cart_item')
            ->from('App:CartItem', 'cart_item')
            ->innerjoin('cart_item.inventory', 'inventory')
            ->where('inventory.isActive = :isActive')
            ->andWhere('inventory.status = :status')
            ->andWhere('inventory.sellerStock >= cart_item.quantity')
            ->andWhere('cart_item.cart = cart.id')
            ->getDQL();

        $query = $this->_em->createQueryBuilder();

        return $this->createQueryBuilder('cart')
                    ->select('PARTIAL cart.{id}')
                    ->innerJoin('cart.customer', 'customer')
                    ->leftJoin('cart.abandonedNotificationLog', 'log')
                    ->where($query->expr()->exists($hasAvailableItemSubQuery))
                    ->andWhere("cart.updatedAt <= :time AND cart.updatedAt >= :two_weeks_ago")
                    ->andWhere('log.cart IS NULL')
                    ->setParameters([
                                        'time'          => $time->format('Y-m-d H:i:s'),
                                        'two_weeks_ago' => date('Y-m-d H:i:s', strtotime('-2 week')),
                                        'isActive'      => true,
                                        'status'        => InventoryStatus::CONFIRMED,
                                    ])
                    ->getQuery()
                    ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
                    ->getResult();
    }

    public function findOneByCardId(string $cartId): ?Cart
    {
        return $this->createQueryBuilder('cart')
            ->select('PARTIAL cart.{id}')
            ->addSelect('PARTIAL customer.{id, mobile, name, family}')
            ->andWhere('cart.id = :cart_id')
            ->innerJoin('cart.cartItems', 'cartItems')
            ->innerJoin('cart.customer', 'customer')
            ->setParameter('cart_id', $cartId)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
