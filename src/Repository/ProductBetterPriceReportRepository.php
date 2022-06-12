<?php

namespace App\Repository;

use App\Entity\ProductBetterPriceReport;
use App\Entity\Customer;
use App\Entity\Product;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductBetterPriceReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductBetterPriceReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductBetterPriceReport[]    findAll()
 * @method ProductBetterPriceReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductBetterPriceReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductBetterPriceReport::class);
    }

    // /**
    //  * @return BetterPriceReport[] Returns an array of BetterPriceReport objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BetterPriceReport
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function countCustomerBetterPriceReportsInSpecificProductAndTime(
        Customer $customer,
        Product $product,
        DateTimeImmutable $time
    ) {
        $result = $this->createQueryBuilder('betterPriceReport')
                       ->select('count(betterPriceReport.id) as count')
                       ->where('betterPriceReport.customer = :customer')
                       ->andWhere('betterPriceReport.product = :product')
                       ->andWhere('betterPriceReport.createdAt > :createdAt')
                       ->setParameters([
                           'customer'    => $customer,
                           'product'    => $product,
                           'createdAt' => $time,
                       ])
                       ->getQuery()
                       ->getResult();

        return (int)$result[0]['count'];
    }
}
