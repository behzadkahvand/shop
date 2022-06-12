<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class ProductVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }

    public static function getMinimumPriceInventoryCriteria(): Criteria
    {
        return Criteria::create()->orderBy(['finalPrice' => Criteria::ASC])->setMaxResults(1);
    }

    public function getCategoryLeadValueByVariantId(int $variantId): int
    {
        try {
            $result = $this->createQueryBuilder('pv')
                           ->leftJoin('pv.product', 'p')
                           ->leftJoin('p.category', 'c')
                           ->where('pv.id = :variantId')
                           ->setParameter('variantId', $variantId)
                           ->groupBy('c.id')
                           ->select('c.maxLeadTime')
                           ->getQuery()
                           ->getSingleScalarResult();

            return (int) ($result ?: -1);
        } catch (\Throwable $e) {
            return -1;
        }
    }

    public function findByProductAndOptions(Product $product, array $optionValueIds): ?ProductVariant
    {
        $countOptions = count($optionValueIds);

        $result = $this->createQueryBuilder('ProductVariant')
                       ->select('ProductVariant')
                       ->innerJoin('ProductVariant.optionValues', 'OptionValues')
                       ->where('ProductVariant.product = :product')
                       ->andWhere('OptionValues.id IN(:optionValueIds)')
                       ->setParameters([
                           'product'        => $product,
                           'optionValueIds' => $optionValueIds,
                           'count'          => $countOptions
                       ])
                       ->having('count(ProductVariant.id) = :count')
                       ->groupBy('ProductVariant.id')
                       ->getQuery()
                       ->getResult();

        if (!empty($result)) {
            foreach ($result as $variant) {
                if (count($variant->getOptionValues()) === $countOptions) {
                    return $variant;
                }
            }
        }

        return null;
    }
}
