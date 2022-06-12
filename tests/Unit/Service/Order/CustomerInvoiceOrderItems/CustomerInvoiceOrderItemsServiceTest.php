<?php

namespace App\Tests\Unit\Service\Order\CustomerInvoiceOrderItems;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Category;
use App\Entity\Configuration;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Order\CustomerInvoiceOrderItems\CustomerInvoiceOrderItemsService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerInvoiceOrderItemsServiceTest extends MockeryTestCase
{
    /**
     * @var ConfigurationServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationServiceMock;

    /**
     * @var Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var OrderItem|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderItemMock;

    /**
     * @var Inventory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $inventoryMock;

    /**
     * @var Seller|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerMock;

    /**
     * @var ProductVariant|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $variantMock;

    /**
     * @var Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productMock;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    protected ?CustomerInvoiceOrderItemsService $customerInvoiceOrderItemsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->configurationMock        = Mockery::mock(Configuration::class);
        $this->orderMock                = Mockery::mock(Order::class);
        $this->orderItemMock            = Mockery::mock(OrderItem::class);
        $this->inventoryMock            = Mockery::mock(Inventory::class);
        $this->sellerMock               = Mockery::mock(Seller::class);
        $this->variantMock              = Mockery::mock(ProductVariant::class);
        $this->productMock              = Mockery::mock(Product::class);
        $this->categoryMock             = Mockery::mock(Category::class);

        $this->customerInvoiceOrderItemsService = new CustomerInvoiceOrderItemsService($this->configurationServiceMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->configurationServiceMock         = null;
        $this->configurationMock                = null;
        $this->orderMock                        = null;
        $this->orderItemMock                    = null;
        $this->inventoryMock                    = null;
        $this->sellerMock                       = null;
        $this->variantMock                      = null;
        $this->productMock                      = null;
        $this->categoryMock                     = null;
        $this->customerInvoiceOrderItemsService = null;
    }

    public function testItJustGetOrderItemsWhenConfigurationsIsNotSet()
    {
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$this->orderItemMock, $this->orderItemMock]));

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_SELLERS)
                                       ->andReturnNull();
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_CATEGORIES)
                                       ->andReturnNull();

        $result = $this->customerInvoiceOrderItemsService->get($this->orderMock);

        self::assertEquals([$this->orderItemMock, $this->orderItemMock], $result);
    }

    public function testItJustGetOrderItemsWhenConfigurationsIsEmpty()
    {
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$this->orderItemMock, $this->orderItemMock]));

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_SELLERS)
                                       ->andReturn($this->configurationMock);
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_CATEGORIES)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->twice()
                                ->withNoArgs()
                                ->andReturnNull();

        $result = $this->customerInvoiceOrderItemsService->get($this->orderMock);

        self::assertEquals([$this->orderItemMock, $this->orderItemMock], $result);
    }

    public function testItCanGetFilteredOrderItems()
    {
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$this->orderItemMock, $this->orderItemMock, $this->orderItemMock]));

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_SELLERS)
                                       ->andReturn($this->configurationMock);
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_CATEGORIES)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn([23], [23], [131], [131]);

        $this->orderItemMock->shouldReceive('getInventory')
                            ->times(3)
                            ->withNoArgs()
                            ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('getSeller')
                            ->times(3)
                            ->withNoArgs()
                            ->andReturn($this->sellerMock);

        $this->sellerMock->shouldReceive('getId')
                         ->times(3)
                         ->withNoArgs()
                         ->andReturn(23, 1, 10);

        $this->inventoryMock->shouldReceive('getVariant')
                            ->times(3)
                            ->withNoArgs()
                            ->andReturn($this->variantMock);

        $this->variantMock->shouldReceive('getProduct')
                          ->times(3)
                          ->withNoArgs()
                          ->andReturn($this->productMock);

        $this->productMock->shouldReceive('getCategory')
                          ->times(3)
                          ->withNoArgs()
                          ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn(54, 131, 20);

        $result = $this->customerInvoiceOrderItemsService->get($this->orderMock);

        self::assertEquals([$this->orderItemMock], $result);
    }
}
