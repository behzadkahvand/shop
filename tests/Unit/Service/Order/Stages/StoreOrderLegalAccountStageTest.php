<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\CustomerLegalAccount;
use App\Entity\Order;
use App\Entity\OrderLegalAccount;
use App\Entity\Province;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\StoreOrderLegalAccountStage;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StoreOrderLegalAccountStageTest extends MockeryTestCase
{
    /**
     * @var CustomerAddress|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerAddressMock;

    /**
     * @var Customer|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerMock;

    /**
     * @var CustomerLegalAccount|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerLegalAccountMock;

    /**
     * @var Province|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $provinceMock;

    /**
     * @var City|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $cityMock;

    protected ?Order $order;

    protected ?CreateOrderPayload $storeOrderPayload;

    protected ?StoreOrderLegalAccountStage $storeOrderLegalAccountStage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressMock      = Mockery::mock(CustomerAddress::class);
        $this->customerMock             = Mockery::mock(Customer::class);
        $this->customerLegalAccountMock = Mockery::mock(CustomerLegalAccount::class);
        $this->provinceMock             = Mockery::mock(Province::class);
        $this->cityMock                 = Mockery::mock(City::class);
        $this->order                    = new Order();

        $this->storeOrderPayload = new CreateOrderPayload(
            Mockery::mock(EntityManagerInterface::class),
            Mockery::mock(Cart::class),
            $this->customerAddressMock,
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $this->storeOrderPayload->setOrder($this->order);

        $this->storeOrderLegalAccountStage = new StoreOrderLegalAccountStage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->customerAddressMock         = null;
        $this->customerMock                = null;
        $this->customerLegalAccountMock    = null;
        $this->order                       = null;
        $this->provinceMock                = null;
        $this->cityMock                    = null;
        $this->storeOrderPayload           = null;
        $this->storeOrderLegalAccountStage = null;
    }

    public function testItCanNotStoreOrderLegalAccount(): void
    {
        $this->customerAddressMock->shouldReceive('getCustomer')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->customerMock);

        $this->customerMock->shouldReceive('isProfileLegal')
                           ->once()
                           ->withNoArgs()
                           ->andReturnFalse();

        $result = $this->storeOrderLegalAccountStage->__invoke($this->storeOrderPayload);

        self::assertEquals($this->storeOrderPayload, $result);
    }

    public function testItCanStoreOrderLegalAccount(): void
    {
        $this->customerAddressMock->shouldReceive('getCustomer')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->customerMock);

        $this->customerMock->shouldReceive('isProfileLegal')
                           ->once()
                           ->withNoArgs()
                           ->andReturnTrue();
        $this->customerMock->shouldReceive('getLegalAccount')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->customerLegalAccountMock);

        $this->customerLegalAccountMock->shouldReceive('getProvince')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->provinceMock);
        $this->customerLegalAccountMock->shouldReceive('getCity')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->cityMock);
        $this->customerLegalAccountMock->shouldReceive('getOrganizationName')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn('Lendo Cooperation');
        $this->customerLegalAccountMock->shouldReceive('getEconomicCode')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn(123456789012);
        $this->customerLegalAccountMock->shouldReceive('getNationalId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("04514565");
        $this->customerLegalAccountMock->shouldReceive('getRegistrationId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("0415/54112");
        $this->customerLegalAccountMock->shouldReceive('getPhoneNumber')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("02188203206");

        $result = $this->storeOrderLegalAccountStage->__invoke($this->storeOrderPayload);

        self::assertEquals($this->storeOrderPayload, $result);

        /**
         * @var OrderLegalAccount $orderLegalAccount
         */
        $orderLegalAccount = $result->getOrder()->getOrderLegalAccounts()->first();

        self::assertEquals($this->order, $orderLegalAccount->getOrder());
        self::assertEquals($this->customerLegalAccountMock, $orderLegalAccount->getCustomerLegalAccount());
        self::assertEquals('Lendo Cooperation', $orderLegalAccount->getOrganizationName());
        self::assertEquals(123456789012, $orderLegalAccount->getEconomicCode());
        self::assertEquals("04514565", $orderLegalAccount->getNationalId());
        self::assertEquals("0415/54112", $orderLegalAccount->getRegistrationId());
        self::assertEquals($this->provinceMock, $orderLegalAccount->getProvince());
        self::assertEquals($this->cityMock, $orderLegalAccount->getCity());
        self::assertEquals("02188203206", $orderLegalAccount->getPhoneNumber());
    }

    public function testGerPriorityAndTag(): void
    {
        self::assertEquals(94, $this->storeOrderLegalAccountStage::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->storeOrderLegalAccountStage::getTag());
    }
}
