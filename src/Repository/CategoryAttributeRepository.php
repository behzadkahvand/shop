<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryAttribute[]    findAll()
 * @method CategoryAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryAttribute::class);
    }

    // /**
    //  * @return CategoryAttribute[] Returns an array of CategoryAttribute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryAttribute
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getCategoryTemplateWithProductAttributeValues(
        Category $category,
        Product $product
    ) {
        $result = $this->createQueryBuilder('CA')
                       ->innerJoin('CA.attribute', 'Attribute')
                       ->addSelect(['Attribute', 'PA'])
                       ->leftJoin(
                           'Attribute.productAttributes',
                           'PA',
                           Join::WITH,
                           'PA.product = :product'
                       )
                       ->where('CA.category = :category')
                       ->setParameters(['product' => $product, 'category' => $category])
                       ->getQuery()
                       ->getResult();

        return $result;
    }
}
