<?php

namespace App\Tests\Unit\Service\Carrier\COD;

use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Service\Carrier\COD\CODService;
use App\Service\Carrier\COD\Condition\CODConditionsAggregator;
use App\Service\Carrier\Exceptions\CODPriceIsNotEquivalentToShipmentPayableException;
use App\Service\Payment\PaymentService;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\TransactionIdentifierService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class CODServiceTest extends MockeryTestCase
{
    /**
     * @var \App\Service\Carrier\COD\Condition\CODConditionsAggregator|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $condition;

    /**
     * @var \App\Service\Payment\PaymentService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $paymentService;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $manager;

    /**
     * @var \App\Service\Payment\TransactionIdentifierService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $transactionIdentifierService;

    /**
     * @var \App\Entity\OrderShipment|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $orderShipment;

    /**
     * @var \App\Service\Payment\Response\Bank\AbstractBankResponse|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $codBankResponse;

    private CODService $codService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condition = m::mock(CODConditionsAggregator::class);
        $this->paymentService = m::mock(PaymentService::class);
        $this->manager = m::mock(EntityManagerInterface::class);
        $this->transactionIdentifierService = m::mock(TransactionIdentifierService::class);

        $this->orderShipment = m::mock(OrderShipment::class);
        $this->codBankResponse = m::mock(AbstractBankResponse::class);

        $this->codService = new CODService(
            $this->condition,
            $this->paymentService,
            $this->manager,
            $this->transactionIdentifierService
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->orderShipment,
            $this->codBankResponse,
            $this->codService,
        );

        $this->condition = null;
        $this->paymentService = null;
        $this->manager = null;
        $this->transactionIdentifierService = null;
    }

    public function testItFailsWhenTransactionAmountIsNotEquivalentToShipmentPayable(): void
    {
        $this->expectException(CODPriceIsNotEquivalentToShipmentPayableException::class);

        $this->codBankResponse->shouldReceive('getAmount')
            ->once()
            ->withNoArgs()
            ->andReturn(1000);

        $this->orderShipment->shouldReceive('getPayable')
            ->once()
            ->withNoArgs()
            ->andReturn(2000);

        $this->codService->registerTransaction($this->orderShipment, $this->codBankResponse);
    }

    public function testItCanRegisterCODTransactionSuccessfully(): void
    {
        $this->codBankResponse->shouldReceive('getAmount')
            ->twice()
            ->withNoArgs()
            ->andReturn(1000);

        $this->orderShipment->shouldReceive('getPayable')
            ->once()
            ->withNoArgs()
            ->andReturn(1000);

        $this->condition->shouldReceive('apply')
            ->once()
            ->with($this->orderShipment)
            ->andReturn();

        $this->orderShipment->shouldReceive('getOrder->getOrderDocument')
            ->once()
            ->withNoArgs()
            ->andReturn();

        $this->transactionIdentifierService->shouldReceive('generateIdentifier')
            ->once()
            ->with(m::type(Transaction::class))
            ->andReturn(123456789);

        $this->manager->shouldReceive('persist')
            ->once()
            ->with(m::type(Transaction::class))
            ->andReturn();

        $this->manager->shouldReceive('flush')
            ->twice()
            ->withNoArgs()
            ->andReturn();

        $this->orderShipment->shouldReceive('setTransaction')
            ->once()
            ->with(m::type(Transaction::class))
            ->andReturn();

        $this->paymentService->shouldReceive('verify')
            ->once()
            ->with(m::type(Transaction::class), $this->codBankResponse)
            ->andReturn();

        $this->codService->registerTransaction($this->orderShipment, $this->codBankResponse);
    }
}
