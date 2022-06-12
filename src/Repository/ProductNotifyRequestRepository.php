<?php

namespace App\Repository;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductNotifyRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductNotifyRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductNotifyRequest[]    findAll()
 * @method ProductNotifyRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductNotifyRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductNotifyRequest::class);
    }

    public function getAllByCustomerQuery($customerId)
    {
        return $this->createQueryBuilder('notify')
                    ->select('notify')
                    ->leftJoin('notify.product', 'Products')
                    ->addSelect('Products')
                    ->where('notify.customer = :customerId')
                    ->setParameters(
                        [
                            'customerId' => $customerId,
                        ]
                    )
                    ->orderBy('notify.createdAt', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    public function findCustomerProductNotifyRequestOnProduct(
        Customer $customer,
        Product $product
    ): ?ProductNotifyRequest {
        return $this
            ->createQueryBuilder('notify')
            ->andWhere('notify.customer = :customer')->setParameter('customer', $customer)
            ->andWhere('notify.product = :product')->setParameter('product', $product)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
