<?php

namespace App\Repository;

use App\Dictionary\InventoryStatus;
use App\Dictionary\ProductStatusDictionary;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Wishlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wishlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wishlist[]    findAll()
 * @method Wishlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function getAllByCustomerQuery($customerId)
    {
        return $this->createQueryBuilder('wishlist')
            ->select('wishlist')
            ->leftJoin('wishlist.product', 'Products')
            ->addSelect('Products')
            ->leftJoin('Products.buyBox', 'buyBox')
            ->where('wishlist.customer = :customerId')
            ->andWhere('Products.status = :confirmed')
            ->andWhere('buyBox.isActive = :active')
            ->andWhere('buyBox.status = :confirmedBuyBox')
            ->andWhere('buyBox.sellerStock > 0')
            ->setParameters([
                'customerId'      => $customerId,
                'confirmed'       => ProductStatusDictionary::CONFIRMED,
                'active'          => true,
                'confirmedBuyBox' => InventoryStatus::CONFIRMED,
            ])
            ->orderBy('wishlist.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
