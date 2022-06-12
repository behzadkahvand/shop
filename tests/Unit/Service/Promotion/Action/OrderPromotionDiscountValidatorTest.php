<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Cart;
use App\Entity\CartPromotionDiscount;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderPromotionDiscount;
use App\Entity\OrderShipment;
use App\Service\Promotion\Action\DiscountValidation\OrderPromotionDiscountValidator;
use App\Service\Promotion\PromotionSubjectInterface;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class OrderPromotionDiscountValidatorTest extends MockeryTestCase
{
    protected OrderPromotionDiscountValidator $orderPromotionDiscountValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderPromotionDiscountValidator = new OrderPromotionDiscountValidator();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->orderPromotionDiscountValidator);

        Mockery::close();
    }

    ### Support testing

    public function testItDoesNotSupportPromotionSubject(): void
    {
        $promotionSubject = Mockery::mock(Cart::class);

        $result = $this->orderPromotionDiscountValidator->supports($promotionSubject);

        self::assertFalse($result);
    }

    public function testItSupportsPromotionSubject(): void
    {
        $promotionSubject = Mockery::mock(Order::class);

        $result = $this->orderPromotionDiscountValidator->supports($promotionSubject);

        self::assertTrue($result);
    }

    ### Apply testing

    public function testItShouldNotApplyWhenOrderItemContextIsNotSet(): void
    {
        $promotionSubject = Mockery::mock(PromotionSubjectInterface::class);

        $result = $this->orderPromotionDiscountValidator->shouldApply($promotionSubject);

        self::assertFalse($result);
    }

    public function testItShouldNotApplyWhenContextIsNotInstanceOfOrderItem(): void
    {
        $promotionSubject = Mockery::mock(PromotionSubjectInterface::class);

        $result = $this->orderPromotionDiscountValidator->shouldApply(
            $promotionSubject,
            [
                'orderItem' => new stdClass()
            ]
        );

        self::assertFalse($result);
    }

    public function testItShouldNotApplyWhenInventoryIdsContextIsSetAndOrderItemInventoryIsNotInIds(): void
    {
        $promotionSubject = Mockery::mock(PromotionSubjectInterface::class);
        $orderItemMock    = Mockery::mock(OrderItem::class);
        $inventoryMock    = Mockery::mock(Inventory::class);

        $orderItemMock->shouldReceive('getInventory')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($inventoryMock);

        $inventoryMock->shouldReceive('getId')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(1);

        $result = $this->orderPromotionDiscountValidator->shouldApply(
            $promotionSubject,
            [
                'orderItem'     => $orderItemMock,
                'inventory_ids' => [3, 6, 9]
            ]
        );

        self::assertFalse($result);
    }

    public function testItShouldNotApplyWhenInventoryIdsContextIsSetAndShipmentHasDiscounts(): void
    {
        $promotionSubject  = Mockery::mock(PromotionSubjectInterface::class);
        $orderItemMock     = Mockery::mock(OrderItem::class);
        $inventoryMock     = Mockery::mock(Inventory::class);
        $orderShipmentMock = Mockery::mock(OrderShipment::class);

        $orderItemMock->shouldReceive('getInventory')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($inventoryMock);

        $inventoryMock->shouldReceive('getId')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(3);

        $orderItemMock->shouldReceive('getOrderShipment')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($orderShipmentMock);

        $orderShipmentMock->shouldReceive('getDiscountsCount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(2);

        $result = $this->orderPromotionDiscountValidator->shouldApply(
            $promotionSubject,
            [
                'orderItem'     => $orderItemMock,
                'inventory_ids' => [3, 6, 9]
            ]
        );

        self::assertFalse($result);
    }

    /**
     * @dataProvider dataProviderShouldApplying
     */
    public function testItShouldApplyingWhenInventoryIdsContextIsSet(
        string $orderShipmentStatus,
        bool $expected
    ): void {
        $promotionSubject  = Mockery::mock(PromotionSubjectInterface::class);
        $orderItemMock     = Mockery::mock(OrderItem::class);
        $inventoryMock     = Mockery::mock(Inventory::class);
        $orderShipmentMock = Mockery::mock(OrderShipment::class);

        $orderItemMock->shouldReceive('getInventory')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($inventoryMock);

        $inventoryMock->shouldReceive('getId')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(3);

        $orderItemMock->shouldReceive('getOrderShipment')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($orderShipmentMock);

        $orderShipmentMock->shouldReceive('getDiscountsCount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(0);
        $orderShipmentMock->shouldReceive('getStatus')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($orderShipmentStatus);

        $result = $this->orderPromotionDiscountValidator->shouldApply(
            $promotionSubject,
            [
                'orderItem'     => $orderItemMock,
                'inventory_ids' => [3, 6, 9]
            ]
        );

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProviderShouldApplying
     */
    public function testItShouldApplyingWhenInventoryIdsContextIsNotSet(
        string $orderShipmentStatus,
        bool $expected
    ): void {
        $promotionSubject  = Mockery::mock(PromotionSubjectInterface::class);
        $orderItemMock     = Mockery::mock(OrderItem::class);
        $orderShipmentMock = Mockery::mock(OrderShipment::class);

        $orderItemMock->shouldReceive('getOrderShipment')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($orderShipmentMock);

        $orderShipmentMock->shouldReceive('getDiscountsCount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(0);
        $orderShipmentMock->shouldReceive('getStatus')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($orderShipmentStatus);

        $result = $this->orderPromotionDiscountValidator->shouldApply(
            $promotionSubject,
            [
                'orderItem' => $orderItemMock,
            ]
        );

        self::assertEquals($expected, $result);
    }

    public function dataProviderShouldApplying(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        $validOrderShipmentStatuses = [
            OrderShipmentStatus::NEW,
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::WAREHOUSE,
        ];

        return array_map(function ($status) use ($validOrderShipmentStatuses) {
            return array($status, (in_array($status, $validOrderShipmentStatuses, true)));
        }, $orderShipmentStatuses);
    }

    ### Revert testing

    public function testItThrowsInvalidArgumentExceptionOnShouldRevertingWhenPromotionDiscountIsInvalid(): void
    {
        $promotionDiscountMock = Mockery::mock(CartPromotionDiscount::class);

        $this->expectException(InvalidArgumentException::class);

        $result = $this->orderPromotionDiscountValidator->shouldRevert($promotionDiscountMock);

        self::assertFalse($result);
    }

    public function testItShouldNotRevertWhenOrderItemNotFound(): void
    {
        $promotionDiscountMock = Mockery::mock(OrderPromotionDiscount::class);

        $promotionDiscountMock->shouldReceive('getOrderItem')
                              ->once()
                              ->withNoArgs()
                              ->andReturnNull();

        $result = $this->orderPromotionDiscountValidator->shouldRevert($promotionDiscountMock);

        self::assertFalse($result);
    }

    public function testItShouldNotRevertWhenOrderShipmentNotFound(): void
    {
        $promotionDiscountMock = Mockery::mock(OrderPromotionDiscount::class);
        $orderItemMock         = Mockery::mock(OrderItem::class);

        $promotionDiscountMock->shouldReceive('getOrderItem')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($orderItemMock);

        $orderItemMock->shouldReceive('getOrderShipment')
                      ->once()
                      ->withNoArgs()
                      ->andReturnNull();

        $result = $this->orderPromotionDiscountValidator->shouldRevert($promotionDiscountMock);

        self::assertFalse($result);
    }

    /**
     * @dataProvider dataProviderShouldReverting
     * @param string $orderShipmentStatus
     * @param bool $expected
     */
    public function testItShouldReverting(string $orderShipmentStatus, bool $expected): void
    {
        $promotionDiscountMock = Mockery::mock(OrderPromotionDiscount::class);
        $orderItemMock         = Mockery::mock(OrderItem::class);
        $orderShipmentMock     = Mockery::mock(OrderShipment::class);

        $promotionDiscountMock->shouldReceive('getOrderItem')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($orderItemMock);

        $orderItemMock->shouldReceive('getOrderShipment')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($orderShipmentMock);

        $orderShipmentMock->shouldReceive('getStatus')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($orderShipmentStatus);

        $result = $this->orderPromotionDiscountValidator->shouldRevert($promotionDiscountMock);

        self::assertEquals($expected, $result);
    }

    public function dataProviderShouldReverting(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        $validOrderShipmentStatuses = [
            OrderShipmentStatus::NEW,
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::WAREHOUSE,
        ];

        return array_map(function ($status) use ($validOrderShipmentStatuses) {
            return array($status, (in_array($status, $validOrderShipmentStatuses, true)));
        }, $orderShipmentStatuses);
    }
}
