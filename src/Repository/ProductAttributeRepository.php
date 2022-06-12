<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttribute[]    findAll()
 * @method ProductAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductAttributeRepository extends ServiceEntityRepository
{
    private int $resultCacheExpireTime;

    public function __construct(ManagerRegistry $registry, int $resultCacheExpireTime)
    {
        parent::__construct($registry, ProductAttribute::class);
        $this->resultCacheExpireTime = $resultCacheExpireTime;
    }

    // /**
    //  * @return ProductAttribute[] Returns an array of ProductAttribute objects
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
    public function findOneBySomeField($value): ?ProductAttribute
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getProductAttributesWithGroup(Product $product, Category $category)
    {
        return $this->createQueryBuilder('PA')
                    ->innerJoin(
                        'PA.attribute',
                        'Attribute',
                        Join::WITH,
                        'PA.product = :product'
                    )
                    ->innerJoin(
                        'Attribute.categoryAttributes',
                        'CA',
                        Join::WITH,
                        'CA.category = :category'
                    )
                    ->innerJoin(
                        'CA.category',
                        'C',
                        Join::WITH,
                        'CA.category = :category'
                    )
                    ->innerJoin('C.categoryAttributeGroups', 'CAG')
                    ->select(['CA', 'PA', 'Attribute', 'CAG', 'C'])
                    ->orderBy('CAG.priority', 'DESC')
                    ->addOrderBy('CA.priority', 'DESC')
                    ->setParameters([
                        'product'  => $product,
                        'category' => $category,
                    ])
                    ->getQuery()
                    ->enableResultCache(
                        $this->resultCacheExpireTime,
                        'customer_product_attributes_' . $product->getId()
                    )
                    ->getResult();
    }
}
