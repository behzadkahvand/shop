<?php

namespace App\Repository;

use App\Dictionary\RateAndReviewStatus;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\RateAndReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RateAndReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method RateAndReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method RateAndReview[]    findAll()
 * @method RateAndReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateAndReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RateAndReview::class);
    }

    public function getFindAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('rate_and_review')->orderBy('rate_and_review.updatedAt', 'DESC');
    }

    public function getFindAllCustomerRateAndReviewsQuery(Customer $customer): QueryBuilder
    {
        return $this
            ->createQueryBuilder('rate_and_review')
            ->andWhere('rate_and_review.customer = :customer')->setParameter('customer', $customer)
            ->orderBy('rate_and_review.updatedAt', 'DESC');
    }

    public function getFindAllAcceptedRateAndReviewsQueryForProduct($product): QueryBuilder
    {
        return $this
            ->createQueryBuilder('rate_and_review')
            ->andWhere('rate_and_review.product = :product')->setParameter('product', $product)
            ->andWhere('rate_and_review.status = :status')->setParameter('status', RateAndReviewStatus::ACCEPTED)
            ->addOrderBy('rate_and_review.pin', 'DESC')
            ->addOrderBy('rate_and_review.updatedAt', 'DESC');
    }

    public function findCustomerRateAndReviewOnProduct(Customer $customer, Product $product): ?RateAndReview
    {
        return $this
            ->createQueryBuilder('rate_and_review')
            ->andWhere('rate_and_review.customer = :customer')->setParameter('customer', $customer)
            ->andWhere('rate_and_review.product = :product')->setParameter('product', $product)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAverageOfRatesForProduct(Product $product): float
    {
        $average = $this
            ->createQueryBuilder('rate_and_review')
            ->select('AVG(rate_and_review.rate) as totalRate')
            ->where('rate_and_review.product = :product')->setParameter('product', $product)
            ->andWhere('rate_and_review.status = :status')->setParameter('status', RateAndReviewStatus::ACCEPTED)
            ->getQuery()
            ->getSingleScalarResult();

        return number_format($average, 1);
    }

    public function findNumbersOfUsersWhoRatedForProduct(Product $product): int
    {
        return (int) $this
            ->createQueryBuilder('rate_and_review')
            ->select('COUNT(rate_and_review.customer) as totalUser')
            ->where('rate_and_review.product = :product')->setParameter('product', $product)
            ->andWhere('rate_and_review.status = :status')->setParameter('status', RateAndReviewStatus::ACCEPTED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRatesPerValueForProduct(Product $product): array
    {
        return $this
            ->createQueryBuilder('rate_and_review')
            ->select('rate_and_review.rate, COUNT(rate_and_review.rate) as ratesPerValue')
            ->groupBy('rate_and_review.rate')
            ->where('rate_and_review.product = :product')->setParameter('product', $product)
            ->andWhere('rate_and_review.status = :status')->setParameter('status', RateAndReviewStatus::ACCEPTED)
            ->getQuery()
            ->getResult();
    }
}
