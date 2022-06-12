<?php

namespace App\Tests\Unit\Service\SellerOrderItem\SellerOrderItemReport;

use App\Entity\Inventory;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\SellerOrderItem\SellerOrderItemReport\SellerOrderItemReportService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerOrderItemReportServiceTest extends MockeryTestCase
{
    /**
     * @var QueryBuilderFilterService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $filterServiceMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;


    /**
     * @var AbstractQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryMock;

    protected ?SellerOrderItemReportService $sellerOrderItemReportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);

        $this->sellerOrderItemReportService = new SellerOrderItemReportService($this->filterServiceMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->filterServiceMock            = null;
        $this->queryBuilderMock             = null;
        $this->queryMock                    = null;
        $this->sellerOrderItemReportService = null;
    }

    public function testItCanGetQueryBuilderWhenNoFilterSet(): void
    {
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(SellerOrderItem::class, [])
                                ->andReturn($this->queryBuilderMock);

        $rootAlias      = 'seller_order_item_alias';
        $orderItemAlias = 'orderItem';
        $inventoryAlias = 'inventory';
        $variantAlias   = 'variant';
        $productAlias   = 'product';
        $sellerAlias    = 'sellerOrderItem';

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("PARTIAL {$rootAlias}.{id, status, description, sendDate}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(SellerOrderItem::class, OrderItem::class)
                                ->andReturnNull();
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(OrderItem::class, Inventory::class)
                                ->andReturnNull();
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Inventory::class, ProductVariant::class)
                                ->andReturnNull();
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(ProductVariant::class, Product::class)
                                ->andReturnNull();
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(SellerOrderItem::class, Seller::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$rootAlias}.orderItem", 'orderItem')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$orderItemAlias}.inventory", 'inventory')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$inventoryAlias}.variant", 'variant')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$variantAlias}.product", 'product')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$rootAlias}.seller", 'sellerOrderItem')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$sellerAlias}.{id, identifier}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$orderItemAlias}.{id, grandTotal, quantity}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$inventoryAlias}.{id, status, sellerStock}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$variantAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$productAlias}.{id, title}")
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$orderItemAlias}.orderShipment", 'orderShipment')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$orderItemAlias}.order", 'orders')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$productAlias}.category", 'category')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$inventoryAlias}.seller", 'sellerInventory')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL orderShipment.{id, deliveryDate, title}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL orders.{id, identifier, status, createdAt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL category.{id, title}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL sellerInventory.{id, identifier}')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $result = $this->sellerOrderItemReportService->getQueryBuilder([]);

        self::assertEquals($this->queryMock, $result);
    }

    public function testItCanGetQueryBuilderWhenFiltersSet(): void
    {
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(SellerOrderItem::class, [
                                    "filter" => [
                                        "seller.id" => 23,
                                        "orderItem.inventory.variant.product.id" => [
                                            "gt" => 0
                                        ]
                                    ]
                                ])
                                ->andReturn($this->queryBuilderMock);

        $rootAlias      = 'seller_order_item_alias';
        $orderItemAlias = 'orderItem';
        $inventoryAlias = 'inventory';
        $variantAlias   = 'variant';
        $productAlias   = 'product';
        $sellerAlias    = 'sellerOrderItem';

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("PARTIAL {$rootAlias}.{id, status, description, sendDate}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(SellerOrderItem::class, OrderItem::class)
                                ->andReturn($orderItemAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(OrderItem::class, Inventory::class)
                                ->andReturn($inventoryAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Inventory::class, ProductVariant::class)
                                ->andReturn($variantAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(ProductVariant::class, Product::class)
                                ->andReturn($productAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(SellerOrderItem::class, Seller::class)
                                ->andReturn($sellerAlias);

        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$sellerAlias}.{id, identifier}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$orderItemAlias}.{id, grandTotal, quantity}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$inventoryAlias}.{id, status, sellerStock}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$variantAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$productAlias}.{id, title}")
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$orderItemAlias}.orderShipment", 'orderShipment')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$orderItemAlias}.order", 'orders')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$productAlias}.category", 'category')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$inventoryAlias}.seller", 'sellerInventory')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL orderShipment.{id, deliveryDate, title}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL orders.{id, identifier, status, createdAt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL category.{id, title}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL sellerInventory.{id, identifier}')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $result = $this->sellerOrderItemReportService->getQueryBuilder([
            "filter" => [
                "seller.id" => 23,
                "orderItem.inventory.variant.product.id" => [
                    "gt" => 0
                ]
            ]
        ]);

        self::assertEquals($this->queryMock, $result);
    }
}
