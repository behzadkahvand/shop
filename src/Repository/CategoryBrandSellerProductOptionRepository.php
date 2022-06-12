<?php

namespace App\Repository;

use App\Entity\CategoryBrandSellerProductOption;
use App\Entity\Product;
use App\Entity\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryBrandSellerProductOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryBrandSellerProductOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryBrandSellerProductOption[]    findAll()
 * @method CategoryBrandSellerProductOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryBrandSellerProductOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryBrandSellerProductOption::class);
    }

    /**
     * @param Product $product
     *
     * @return array|CategoryBrandSellerProductOption[]
     */
    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('cbsg')
                    ->select('PARTIAL cbsg.{id}')
                    ->where('cbsg.category = :category AND cbsg.brand = :brand')
                    ->andWhere('cbsg.productOption IN (:options)')
                    ->leftJoin('cbsg.values', 'values')
                    ->setParameters([
                        'category' => $product->getCategory(),
                        'brand'    => $product->getBrand(),
                        'options'  => $product->getOptions(),
                    ])
                    ->getQuery()
                    ->getResult();
    }
}
