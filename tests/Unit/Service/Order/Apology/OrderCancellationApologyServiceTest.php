<?php

namespace App\Tests\Unit\Service\Order\Apology;

use App\Entity\Apology;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderCancelReason;
use App\Entity\OrderCancelReasonOrder;
use App\Exceptions\Apology\FailedToFindApologyForCancelReasonException;
use App\Repository\OrderCancelReasonApologyRepository;
use App\Repository\OrderCancelReasonOrderRepository;
use App\Service\Apology\ApologyService;
use App\Service\Order\Apology\OrderCancellationApologyService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderCancellationApologyServiceTest extends MockeryTestCase
{
    /**
     * @var OrderCancelReasonOrderRepository|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $orderCancelReasonOrderRepository;

    /**
     * @var ApologyService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $apologyService;

    private OrderCancellationApologyService $sut;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $order;

    /**
     * @var OrderCancelReasonOrder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $orderCancelReason;

    /**
     * @var OrderCancelReason|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $cancelReason;

    /**
     * @var Apology|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $apology;

    /**
     * @var Customer|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $customer;

    /**
     * @var OrderCancelReasonApologyRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $orderCancelReasonApologyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderCancelReasonOrderRepository   = Mockery::mock(
            OrderCancelReasonOrderRepository::class
        );
        $this->orderCancelReasonApologyRepository = Mockery::mock(
            OrderCancelReasonApologyRepository::class
        );
        $this->apologyService                     = Mockery::mock(
            ApologyService::class
        );
        $this->order                              = Mockery::mock(Order::class);
        $this->orderCancelReason                  = Mockery::mock(
            OrderCancelReasonOrder::class
        );
        $this->cancelReason                       = Mockery::mock(
            OrderCancelReason::class
        );
        $this->apology                            = Mockery::mock(
            Apology::class
        );
        $this->customer                           = Mockery::mock(
            Customer::class
        );

        $this->sut = new OrderCancellationApologyService(
            $this->orderCancelReasonOrderRepository,
            $this->orderCancelReasonApologyRepository,
            $this->apologyService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);
        $this->orderCancelReasonOrderRepository   = null;
        $this->orderCancelReasonApologyRepository = null;
        $this->apologyService                     = null;
        $this->order                              = null;
        $this->orderCancelReason                  = null;
        $this->cancelReason                       = null;
        $this->apology                            = null;
        $this->customer                           = null;
    }

    public function testShouldCallApologyServiceWithExpectedArguments(): void
    {
        $this->orderCancelReasonOrderRepository
            ->shouldReceive('findOneBy')
            ->once()->with(['order' => $this->order])->andReturn(
                $this->orderCancelReason
            );

        $this->orderCancelReason
            ->shouldReceive('getCancelReason')
            ->once()->withNoArgs()->andReturn($this->cancelReason);

        $this->orderCancelReasonApologyRepository
            ->shouldReceive('findApologyByCancelReason')
            ->once()->with($this->cancelReason)->andReturn($this->apology);

        $this->order
            ->shouldReceive('getCustomer')
            ->once()->withNoArgs()->andReturn($this->customer);

        $this->apologyService
            ->shouldReceive('apologize')
            ->once()->with([$this->customer], $this->apology);

        $this->sut->apologize($this->order);
    }

    public function testShouldThrowExceptionIfCancelReasonIsNotAssociatedWithAnyApology(): void
    {
        $this->orderCancelReasonOrderRepository
            ->shouldReceive('findOneBy')
            ->once()->with(['order' => $this->order])->andReturn(
                $this->orderCancelReason
            );

        $this->orderCancelReason
            ->shouldReceive('getCancelReason')
            ->once()->withNoArgs()->andReturn($this->cancelReason);
        $this->orderCancelReasonApologyRepository
            ->shouldReceive('findApologyByCancelReason')
            ->once()->with($this->cancelReason)->andReturnNull();

        $this->expectException(
            FailedToFindApologyForCancelReasonException::class
        );

        $this->sut->apologize($this->order);
    }

    public function testShouldDoNothingIfOrderDoesNotHaveCancelReason(): void
    {
        $this->orderCancelReasonOrderRepository
            ->shouldReceive('findOneBy')
            ->once()->with(['order' => $this->order])->andReturnNull();

        $this->sut->apologize($this->order);
    }
}
