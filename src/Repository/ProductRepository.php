<?php

namespace App\Repository;

use App\Dictionary\DefaultProductOptionCode;
use App\Dictionary\InventoryStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\ProductStatusDictionary;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Seller;
use App\Service\Product\Exceptions\ProductNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getSellerProductById(int $productId, Seller $seller): Product
    {
        $product = $this->createQueryBuilder('Products')
                        ->addSelect(['productVariants', 'inventories'])
                        ->leftJoin('Products.productVariants', 'productVariants')
                        ->leftJoin(
                            'productVariants.inventories',
                            'inventories',
                            Join::WITH,
                            'inventories.seller = :seller'
                        )
                        ->where('Products.id = :productId')
                        ->setParameters([
                            'productId' => $productId,
                            'seller'    => $seller,
                        ])
                        ->getQuery()
                        ->getResult();

        if (count($product) === 0) {
            throw new ProductNotFoundException();
        }

        return $product[0];
    }

    public function listByCategories(array $categoryIds, int $maxResults = 0): QueryBuilder
    {
        $ids = $this->createQueryBuilder('Products')
                    ->select('Products.id')
                    ->innerJoin('Products.buyBox', 'BuyBox')
                    ->where('Products.category IN(:categoryIds)')
                    ->andWhere('Products.status = :confirmed')
                    ->setParameters([
                        'categoryIds' => $categoryIds,
                        'confirmed'   => ProductStatusDictionary::CONFIRMED,
                    ])
                    ->addOrderBy('Products.orderCount', 'DESC')
                    ->groupBy('Products.id');

        if (0 < $maxResults) {
            $ids->setMaxResults($maxResults);
        }

        $ids = $ids->getQuery()->getScalarResult();

        return $this->createQueryBuilder('product')
                    ->where('product.id IN (:ids)')
                    ->innerJoin('product.buyBox', 'buyBox')
                    ->innerJoin('product.productVariants', 'productVariants')
                    ->innerJoin('product.category', 'category')
                    ->innerJoin('productVariants.inventories', 'inventories')
                    ->leftJoin('productVariants.optionValues', 'optionValues')
                    ->innerJoin('optionValues.option', 'option')
                    ->leftJoin('product.featuredImage', 'image')
                    ->leftJoin('inventories.seller', 'seller')
                    ->leftJoin('category.discountRange', 'discount_range')
                    ->leftJoin('category.categoryProductIdentifier', 'cpi')
                    ->select('PARTIAL product.{id, title, subtitle, alternativeTitle, colors, status}')
                    ->addSelect('PARTIAL productVariants.{id, code}')
                    ->addSelect('PARTIAL image.{id, path, alt}')
                    ->addSelect('PARTIAL seller.{id, identifier, name}')
                    ->addSelect('PARTIAL buyBox.{id, price, finalPrice, leadTime}')
                    ->addSelect('PARTIAL category.{id, code, commission, maxLeadTime}')
                    ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                    ->addSelect('PARTIAL option.{id, code}')
                    ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock, hasCampaign}')
                    ->addSelect('PARTIAL discount_range.{id}')
                    ->addSelect('PARTIAL cpi.{id}')
                    ->setParameter('ids', array_column($ids, 'id'))
                    ->orderBy(sprintf('FIELD(product.id, \'%s\')', implode("','", array_column($ids, 'id'))));
    }

    public function getProductIdsFromItemCollection(Collection $itemCollection)
    {
        $inventoryProductMap = [];
        // TODO use query builder for better performance
        $itemCollection->forAll(function ($key, $item) use (&$inventoryProductMap) {
            $productId   = $item->getInventory()->getVariant()->getProduct()->getId();
            $inventoryId = $item->getInventory()->getId();

            if (!isset($inventoryProductMap[$item->getInventory()->getId()])) {
                $inventoryProductMap[$productId] = [];
            }
            $inventoryProductMap[$productId][] = $inventoryId;

            return true;
        });

        return $inventoryProductMap;
    }

    public function listByIds(array $ids = []): array
    {
        return $this->createQueryBuilder('product')
                    ->where('product.id IN (:ids) and product.status = :status')
                    ->setParameters(['ids' => $ids, 'status' => ProductStatusDictionary::CONFIRMED])
                    ->innerJoin('product.buyBox', 'buyBox')
                    ->innerJoin('product.productVariants', 'productVariants')
                    ->innerJoin('product.category', 'category')
                    ->innerJoin('productVariants.inventories', 'inventories')
                    ->leftJoin('productVariants.optionValues', 'optionValues')
                    ->innerJoin('optionValues.option', 'option')
                    ->leftJoin('product.featuredImage', 'image')
                    ->leftJoin('inventories.seller', 'seller')
                    ->leftJoin('category.discountRange', 'discount_range')
                    ->leftJoin('category.categoryProductIdentifier', 'cpi')
                    ->select('PARTIAL product.{id, title, subtitle, alternativeTitle, colors, status}')
                    ->addSelect('PARTIAL productVariants.{id, code}')
                    ->addSelect('PARTIAL image.{id, path, alt}')
                    ->addSelect('PARTIAL seller.{id, identifier, name}')
                    ->addSelect('PARTIAL buyBox.{id, price, finalPrice, leadTime}')
                    ->addSelect('PARTIAL category.{id, code, commission, maxLeadTime}')
                    ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                    ->addSelect('PARTIAL option.{id, code}')
                    ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock, hasCampaign}')
                    ->addSelect('PARTIAL discount_range.{id}')
                    ->addSelect('PARTIAL cpi.{id}')
                    ->orderBy(sprintf('FIELD(product.id, \'%s\')', implode("','", $ids)))
                    ->getQuery()
                    ->setHint(Query::HINT_REFRESH, true)
                    ->getResult();
    }

    public function getProductIdsHasInventory(): array
    {
        $result = $this->createQueryBuilder('Products')
                       ->select('DISTINCT Products.id')
                       ->innerJoin('Products.productVariants', 'ProductVariants')
                       ->innerJoin('ProductVariants.inventories', 'Inventory')
                       ->where('Inventory.id > 0')
                       ->getQuery()
                       ->getResult();

        return array_column($result, 'id');
    }

    public function getAvailableProductIds(): array
    {
        $result = $this->createQueryBuilder('Products')
                       ->select('DISTINCT Products.id')
                       ->innerJoin('Products.productVariants', 'ProductVariants')
                       ->innerJoin('ProductVariants.inventories', 'Inventory')
                       ->where('Inventory.isActive = :isActive')
                       ->andWhere('Products.status = :status')
                       ->andWhere('Inventory.sellerStock > 0')
                       ->setParameters([
                           'isActive' => true,
                           'status'   => InventoryStatus::CONFIRMED,
                       ])
                       ->getQuery()
                       ->getResult();

        return array_column($result, 'id');
    }

    public function listByCategoriesWithPromotion(
        array $categoryIds,
        int   $maxResults = 0,
        bool  $random = false
    ): QueryBuilder {
        $ids = $this->createQueryBuilder('Products')
                    ->select('Products.id')
                    ->innerJoin('Products.buyBox', 'buyBox')
                    ->where('buyBox.finalPrice < buyBox.price')
                    ->andWhere('Products.status = :confirmed')
                    ->setParameters([
                        'confirmed' => ProductStatusDictionary::CONFIRMED,
                    ])
                    ->groupBy('Products.id');

        if (0 < count($categoryIds)) {
            $ids->andWhere('Products.category IN(:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        if (0 < $maxResults) {
            $ids->setMaxResults($maxResults);
        }

        if ($random) {
            $ids->addOrderBy('RAND()');
        }

        $ids = $ids->getQuery()
                   ->getScalarResult();

        return $this->createQueryBuilder('product')
                    ->where('product.id IN (:ids)')
                    ->setParameter('ids', array_column($ids, 'id'))
                    ->innerJoin('product.buyBox', 'buyBox')
                    ->innerJoin('product.productVariants', 'productVariants')
                    ->innerJoin('product.category', 'category')
                    ->innerJoin('productVariants.inventories', 'inventories')
                    ->leftJoin('productVariants.optionValues', 'optionValues')
                    ->innerJoin('optionValues.option', 'option')
                    ->leftJoin('product.featuredImage', 'image')
                    ->leftJoin('inventories.seller', 'seller')
                    ->leftJoin('category.discountRange', 'discount_range')
                    ->leftJoin('category.categoryProductIdentifier', 'cpi')
                    ->select('PARTIAL product.{id, title, subtitle, alternativeTitle, colors, status}')
                    ->addSelect('PARTIAL productVariants.{id, code}')
                    ->addSelect('PARTIAL image.{id, path, alt}')
                    ->addSelect('PARTIAL seller.{id, identifier, name}')
                    ->addSelect('PARTIAL buyBox.{id, price, finalPrice, leadTime}')
                    ->addSelect('PARTIAL category.{id, code, commission, maxLeadTime}')
                    ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                    ->addSelect('PARTIAL option.{id, code}')
                    ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock, hasCampaign}')
                    ->addSelect('PARTIAL discount_range.{id}')
                    ->addSelect('PARTIAL cpi.{id}')
                    ->orderBy(sprintf('FIELD(product.id, \'%s\')', implode("','", array_column($ids, 'id'))));
    }

    public function getSellerProductsQueryBuilder(Seller $seller, bool $checkInventories): QueryBuilder
    {
        $qb = $this->createQueryBuilder('product')
                   ->addSelect('productVariants', 'inventories')
                   ->leftJoin('product.productVariants', 'productVariants')
                   ->leftJoin('productVariants.inventories', 'inventories')
                   ->where('product.seller = :seller')
                   ->setParameter('seller', $seller);

        if ($checkInventories) {
            $qb->orWhere('inventories.seller = :seller');
        }

        return $qb;
    }

    public function getCountBuyBoxForSeller(Seller $seller)
    {
        $result = $this->createQueryBuilder('Product')
                       ->select('count(Product.id) as count')
                       ->innerJoin('Product.buyBox', 'Inventory')
                       ->where('Inventory.seller = :seller')
                       ->setParameter('seller', $seller)
                       ->getQuery()
                       ->getResult();

        return (int) $result[0]['count'];
    }

    public function findProductsForAllCustomerDeliveredOrders(Customer $customer): QueryBuilder
    {
        $filters = [
            'inventoryIsActive',
            'inventoryHasStock',
            'inventoryConfirmedStatus',
        ];

        foreach ($filters as $filter) {
            $this->_em->getFilters()->disable($filter);
        }

        $subQuery = 'SELECT IDENTITY(rates.product) FROM App\Entity\RateAndReview rates ';
        $subQuery .= 'WHERE rates.customer = :customer';

        return $this->createQueryBuilder('product')
                    ->innerJoin('product.productVariants', 'product_variant')
                    ->innerJoin('product_variant.inventories', 'inventory')
                    ->innerJoin('inventory.orderItems', 'order_item')
                    ->innerJoin('order_item.order', 'ords')
                    ->where("product.id NOT IN ({$subQuery})")
                    ->andWhere('product.status IN (:product_status)')
                    ->andWhere('ords.status = :order_status')
                    ->andWhere('ords.customer = :customer')
                    ->setParameter('customer', $customer)
                    ->setParameter('order_status', OrderStatus::DELIVERED)
                    ->setParameter('product_status', [
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::SHUTDOWN,
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::UNAVAILABLE,
                    ], Connection::PARAM_STR_ARRAY)
                    ->groupBy('product.id')
                    ->orderBy('product.id', 'DESC');
    }

    public function getSimilarProducts(Product $product): array
    {
        $similarProducts = $this->createQueryBuilder('product')
                                ->select('product.id')
                                ->distinct(true)
                                ->innerJoin('product.buyBox', 'buy_box')
                                ->innerJoin('product.productVariants', 'product_variants')
                                ->innerJoin('product_variants.inventories', 'inventories')
                                ->where('product.category = :category AND product.id != :id AND product.status = :status')
                                ->orderBy('RAND()')
                                ->setMaxResults(11)
                                ->setParameters([
                                    'id'       => $product->getId(),
                                    'category' => $product->getCategory(),
                                    'status'   => ProductStatusDictionary::CONFIRMED,
                                ])
                                ->getQuery()
                                ->getResult();

        $ids = array_column($similarProducts, 'id');

        return $this->createQueryBuilder('product')
                    ->where('product.id IN (:ids)')
                    ->setParameters(compact('ids'))
                    ->innerJoin('product.buyBox', 'buyBox')
                    ->innerJoin('product.productVariants', 'productVariants')
                    ->innerJoin('product.category', 'category')
                    ->innerJoin('productVariants.inventories', 'inventories')
                    ->leftJoin('productVariants.optionValues', 'optionValues')
                    ->innerJoin('optionValues.option', 'option')
                    ->leftJoin('product.featuredImage', 'image')
                    ->leftJoin('inventories.seller', 'seller')
                    ->leftJoin('category.discountRange', 'discount_range')
                    ->leftJoin('category.categoryProductIdentifier', 'cpi')
                    ->select('PARTIAL product.{id, title, subtitle, alternativeTitle, status}')
                    ->addSelect('PARTIAL productVariants.{id, code}')
                    ->addSelect('PARTIAL image.{id, path, alt}')
                    ->addSelect('PARTIAL seller.{id, identifier, name}')
                    ->addSelect('PARTIAL buyBox.{id, price, finalPrice, leadTime}')
                    ->addSelect('PARTIAL category.{id, code, commission, maxLeadTime}')
                    ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                    ->addSelect('PARTIAL option.{id, code}')
                    ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                    ->addSelect('PARTIAL discount_range.{id}')
                    ->addSelect('PARTIAL cpi.{id}')
                    ->orderBy(sprintf('FIELD(product.id, \'%s\')', implode("','", $ids)))
                    ->getQuery()
                    ->setHint(Query::HINT_REFRESH, true)
                    ->getResult();
    }

    public function show(int $id): ?Product
    {
        $result = $this->createQueryBuilder('product')
                       ->where('product.id = :id')
                       ->setParameters(compact('id'))
                       ->leftJoin('product.productVariants', 'productVariants')
                       ->innerJoin('product.brand', 'brand')
                       ->innerJoin('product.shippingCategory', 'shippingCategory')
                       ->innerJoin('product.category', 'category')
                       ->leftJoin('product.buyBox', 'buyBox')
                       ->leftJoin('product.productIdentifiers', 'productIdentifiers')
                       ->leftJoin('product.featuredImage', 'featuredImage')
                       ->leftJoin('productVariants.inventories', 'inventories')
                       ->leftJoin('productVariants.optionValues', 'optionValues')
                       ->leftJoin('optionValues.option', 'option')
                       ->leftJoin('inventories.seller', 'seller')
                       ->leftJoin('seller.score', 'sellerScore')
                       ->leftJoin('category.discountRange', 'discount_range')
                       ->leftJoin('category.categoryProductIdentifier', 'cpi')
                       ->select('PARTIAL product.{id, title, subtitle, description, metaDescription, isActive, isOriginal, status, length, width, height, weight, EAV, summaryEAV}')
                       ->addSelect('PARTIAL productVariants.{id, code}')
                       ->addSelect('PARTIAL brand.{id, title, code, subtitle, metaDescription}')
                       ->addSelect('PARTIAL shippingCategory.{id, name}')
                       ->addSelect('PARTIAL category.{id, code, title, pageTitle, subtitle, commission, maxLeadTime}')
                       ->addSelect('PARTIAL discount_range.{id}')
                       ->addSelect('PARTIAL cpi.{id}')
                       ->addSelect('PARTIAL buyBox.{id, price, finalPrice, leadTime, hasCampaign}')
                       ->addSelect('PARTIAL productIdentifiers.{id, identifier}')
                       ->addSelect('PARTIAL featuredImage.{id, path, alt}')
                       ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, hasCampaign}')
                       ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                       ->addSelect('PARTIAL option.{id, code, name}')
                       ->addSelect('PARTIAL seller.{id, identifier, name}')
                       ->addSelect('PARTIAL sellerScore.{id, returnScore, deliveryDelayScore, orderCancellationScore, totalScore}')
                       ->getQuery()
                       ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    public function findProductsByInventoryIds(array $inventoryIds = []): array
    {
        return $this->createQueryBuilder('product')
                    ->where('inventories.id IN (:ids)')
                    ->setParameters(['ids' => $inventoryIds])
                    ->innerJoin('product.productVariants', 'productVariants')
                    ->innerJoin('productVariants.inventories', 'inventories')
                    ->leftJoin('productVariants.optionValues', 'optionValues')
                    ->leftJoin('product.featuredImage', 'image')
                    ->leftJoin('inventories.seller', 'seller')
                    ->select('PARTIAL product.{id, title, subtitle, alternativeTitle, colors, status}')
                    ->addSelect('PARTIAL productVariants.{id, code}')
                    ->addSelect('PARTIAL image.{id, path, alt}')
                    ->addSelect('PARTIAL seller.{id, identifier, name}')
                    ->addSelect('PARTIAL optionValues.{id, value, code, attributes}')
                    ->addSelect('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock, hasCampaign}')
                    ->orderBy(sprintf(
                        'FIELD(inventories.id, \'%s\')',
                        implode("','", $inventoryIds)
                    ))
                    ->getQuery()
                    ->setHint(Query::HINT_REFRESH, true)
                    ->getResult();
    }

    public function findByTitleOrDigikalaDkp(string $title, string $digikalaDkp): ?Product
    {
        $result = $this->createQueryBuilder('product')
                       ->where('product.title = :title')
                       ->orWhere('product.digikalaDkp = :digikalaDkp')
                       ->setParameters([
                           'title'       => $title,
                           'digikalaDkp' => $digikalaDkp,
                       ])
                       ->getQuery()
                       ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    public function getAllActiveProductsCount(): int
    {
        return $this->createQueryBuilder('product')
                    ->select('COUNT(product.id)')
                    ->where('product.isActive = :isActive')
                    ->andWhere('product.status IN (:statuses)')
                    ->setParameters([
                        'isActive' => true,
                        'statuses' => [
                            ProductStatusDictionary::SOON,
                            ProductStatusDictionary::CONFIRMED,
                            ProductStatusDictionary::UNAVAILABLE,
                            ProductStatusDictionary::SHUTDOWN,
                        ],
                    ])
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function getProductsBatchForElasticSync(int $offset, int $limit): array
    {
        return $this->createQueryBuilder('product')
                    ->where('product.isActive = :isActive')
                    ->andWhere('product.status IN (:statuses)')
                    ->setParameters([
                        'isActive' => true,
                        'statuses' => [
                            ProductStatusDictionary::SOON,
                            ProductStatusDictionary::CONFIRMED,
                            ProductStatusDictionary::UNAVAILABLE,
                            ProductStatusDictionary::SHUTDOWN,
                        ],
                    ])
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }
}
