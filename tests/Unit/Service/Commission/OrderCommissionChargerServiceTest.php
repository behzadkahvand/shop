<?php

namespace App\Tests\Unit\Service\Commission;

use App\Entity\Brand;
use App\Entity\CampaignCommission;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Seller;
use App\Repository\CampaignCommissionRepository;
use App\Service\Commission\OrderCommissionChargerService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class OrderCommissionChargerServiceTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|CampaignCommissionRepository|null $campaignCommissionRepo;
    private OrderCommissionChargerService|null $sut;
    private Order|LegacyMockInterface|MockInterface|null $order;
    private Category|LegacyMockInterface|MockInterface|null $category;
    private Brand|LegacyMockInterface|MockInterface|null $brand;
    private Seller|LegacyMockInterface|MockInterface|null $seller;
    private LegacyMockInterface|OrderItem|MockInterface|null $orderItem;
    private CampaignCommission|LegacyMockInterface|MockInterface|null $campaignCommission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = Mockery::mock(Order::class);
        $this->category = Mockery::mock(Category::class);
        $this->brand = Mockery::mock(Brand::class);
        $this->seller = Mockery::mock(Seller::class);
        $this->orderItem = Mockery::mock(OrderItem::class);
        $this->campaignCommission = Mockery::mock(CampaignCommission::class);
        $this->campaignCommissionRepo = Mockery::mock(CampaignCommissionRepository::class);
        $this->sut = new OrderCommissionChargerService($this->campaignCommissionRepo);
    }

    public function testShouldChargeOrderItemsWithCampaignCommissionIfCampaignCommissionExists(): void
    {
        $this->order->shouldReceive('getItems')->once()->withNoArgs()->andReturn(collect([$this->orderItem]));
        $this->orderItem->shouldReceive('getCategory')->once()->withNoArgs()->andReturn($this->category);
        $this->orderItem->shouldReceive('getBrand')->once()->withNoArgs()->andReturn($this->brand);
        $this->orderItem->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->campaignCommissionRepo
            ->shouldReceive('findActiveCommission')
            ->once()
            ->with($this->category, $this->brand, $this->seller)
            ->andReturn($this->campaignCommission);
        $fee = 1.6;
        $this->campaignCommission->shouldReceive('getFee')->once()->withNoArgs()->andReturn($fee);
        $this->orderItem->shouldReceive('setCommission')->once()->with($fee)->andReturnSelf();

        $this->sut->charge($this->order);
    }

    public function testShouldChargeOrderItemsWithDefaultCategoryCommissionIfCampaignCommissionDoesNotExist(): void
    {
        $this->order->shouldReceive('getItems')->once()->withNoArgs()->andReturn(collect([$this->orderItem]));
        $this->orderItem->shouldReceive('getCategory')->twice()->withNoArgs()->andReturn($this->category);
        $this->orderItem->shouldReceive('getBrand')->once()->withNoArgs()->andReturn($this->brand);
        $this->orderItem->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->campaignCommissionRepo
            ->shouldReceive('findActiveCommission')
            ->once()
            ->with($this->category, $this->brand, $this->seller)
            ->andReturnNull();
        $fee = 2.5;
        $this->category->shouldReceive('getCommission')->once()->andReturn($fee);
        $this->orderItem->shouldReceive('setCommission')->once()->with($fee)->andReturnSelf();

        $this->sut->charge($this->order);
    }

    public function testShouldWorkWhenSomeOfOrderItemsHaveCampaignCommissionAndSomeDont(): void
    {
        $itemWithCampaignCommission = Mockery::mock(OrderItem::class);
        $itemWithoutCampaignCommission = Mockery::mock(OrderItem::class);

        $campaignCategory = Mockery::mock(Category::class);
        $normalCategory = Mockery::mock(Category::class);

        $campaignBrand = Mockery::mock(Brand::class);
        $normalBrand = Mockery::mock(Brand::class);

        $campaignSeller = Mockery::mock(Seller::class);
        $normalSeller = Mockery::mock(Seller::class);

        $this->order
            ->shouldReceive('getItems')
            ->once()
            ->withNoArgs()
            ->andReturn(collect([$itemWithCampaignCommission, $itemWithoutCampaignCommission]));

        $itemWithCampaignCommission->shouldReceive('getCategory')->once()->withNoArgs()->andReturn($campaignCategory);
        $itemWithCampaignCommission->shouldReceive('getBrand')->once()->withNoArgs()->andReturn($campaignBrand);
        $itemWithCampaignCommission->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($campaignSeller);

        $itemWithoutCampaignCommission->shouldReceive('getCategory')->twice()->withNoArgs()->andReturn($normalCategory);
        $itemWithoutCampaignCommission->shouldReceive('getBrand')->once()->withNoArgs()->andReturn($normalBrand);
        $itemWithoutCampaignCommission->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($normalSeller);

        $this->campaignCommissionRepo
            ->shouldReceive('findActiveCommission')
            ->once()
            ->with($campaignCategory, $campaignBrand, $campaignSeller)
            ->andReturn($this->campaignCommission);
        $this->campaignCommissionRepo
            ->shouldReceive('findActiveCommission')
            ->once()
            ->with($normalCategory, $normalBrand, $normalSeller)
            ->andReturnNull();

        $campaignFee = 1.6;
        $this->campaignCommission->shouldReceive('getFee')->once()->withNoArgs()->andReturn($campaignFee);

        $normalCategoryFee = 2.5;
        $normalCategory->shouldReceive('getCommission')->once()->andReturn($normalCategoryFee);

        $itemWithCampaignCommission->shouldReceive('setCommission')->once()->with($campaignFee)->andReturnSelf();
        $itemWithoutCampaignCommission->shouldReceive('setCommission')->once()->with($normalCategoryFee)->andReturnSelf();

        $this->sut->charge($this->order);
    }
}
