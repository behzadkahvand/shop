<?php

namespace App\Tests\Unit\Service\Order\ReturnRequest;

use App\Entity\Customer;
use App\Entity\ReturnRequest;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestApprovedNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsRefundedNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsWaitingRefundNotificationDTO;
use App\Service\Order\ReturnRequest\Notification\ReturnRequestNotificationFactory;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestStatus;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ReturnRequestNotificationFactoryTest extends BaseUnitTestCase
{
    private ReturnRequestNotificationFactory|null $sut;
    private ReturnRequest|LegacyMockInterface|MockInterface|null $returnRequest;
    private LegacyMockInterface|MockInterface|Customer|null $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Mockery::mock(Customer::class);
        $this->returnRequest = Mockery::mock(ReturnRequest::class);
        $this->sut = new ReturnRequestNotificationFactory();
    }

    public function testForApprovedStatus(): void
    {
        $this->returnRequest->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(ReturnRequestStatus::APPROVED);
        $this->returnRequest->shouldReceive('getOrder->getCustomer')->once()->withNoArgs()->andReturn($this->customer);

        $notification = $this->sut->make($this->returnRequest);

        self::assertNotNull($notification);
        self::assertInstanceOf(ReturnRequestApprovedNotificationDTO::class, $notification);
    }

    public function testForIsWaitingRefundStatus(): void
    {
        $this->returnRequest->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(ReturnRequestStatus::WAITING_REFUND);
        $this->returnRequest->shouldReceive('getOrder->getCustomer')->once()->withNoArgs()->andReturn($this->customer);

        $notification = $this->sut->make($this->returnRequest);

        self::assertNotNull($notification);
        self::assertInstanceOf(ReturnRequestIsWaitingRefundNotificationDTO::class, $notification);
    }

    public function testForRefundedStatus(): void
    {
        $this->returnRequest->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(ReturnRequestStatus::REFUNDED);
        $this->returnRequest->shouldReceive('getOrder->getCustomer')->once()->withNoArgs()->andReturn($this->customer);

        $notification = $this->sut->make($this->returnRequest);

        self::assertNotNull($notification);
        self::assertInstanceOf(ReturnRequestIsRefundedNotificationDTO::class, $notification);
    }

    /**
     * @dataProvider statusWithoutNotificationProvider
     */
    public function testForOtherStatuses($status): void
    {
        $this->returnRequest->shouldReceive('getStatus')->once()->withNoArgs()->andReturn($status);
        $this->returnRequest->shouldReceive('getOrder->getCustomer')->once()->withNoArgs()->andReturn($this->customer);

        $notification = $this->sut->make($this->returnRequest);

        self::assertNull($notification);
    }

    public function statusWithoutNotificationProvider(): array
    {
        return [
            [ReturnRequestStatus::RETURNED],
            [ReturnRequestStatus::RETURNING],
        ];
    }
}
