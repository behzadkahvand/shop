<?php

namespace App\Tests\Unit\Service\OrderLegalAccount;

use App\Dictionary\OrderStatus;
use App\DTO\Admin\OrderLegalAccountData;
use App\Entity\City;
use App\Entity\CustomerLegalAccount;
use App\Entity\Order;
use App\Entity\OrderLegalAccount;
use App\Entity\Province;
use App\Service\OrderLegalAccount\Exceptions\StoreOrderLegalAccountException;
use App\Service\OrderLegalAccount\OrderLegalAccountFactory;
use App\Service\OrderLegalAccount\OrderLegalAccountService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderLegalAccountServiceTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var OrderLegalAccountFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var CustomerLegalAccount|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerLegalAccountMock;

    /**
     * @var OrderLegalAccount|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderLegalAccountMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var Province|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $provinceMock;

    /**
     * @var City|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $cityMock;

    /**
     * @var OrderLegalAccountData|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderLegalAccountDTOMock;

    protected ?OrderLegalAccountService $orderLegalAccountService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                       = Mockery::mock(EntityManagerInterface::class);
        $this->factoryMock              = Mockery::mock(OrderLegalAccountFactory::class);
        $this->customerLegalAccountMock = Mockery::mock(CustomerLegalAccount::class);
        $this->orderLegalAccountMock    = Mockery::mock(OrderLegalAccount::class);
        $this->orderMock                = Mockery::mock(Order::class);
        $this->provinceMock             = Mockery::mock(Province::class);
        $this->cityMock                 = Mockery::mock(City::class);
        $this->orderLegalAccountDTOMock = Mockery::mock(OrderLegalAccountData::class);

        $this->orderLegalAccountService = new OrderLegalAccountService(
            $this->em,
            $this->factoryMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em                       = null;
        $this->factoryMock              = null;
        $this->customerLegalAccountMock = null;
        $this->orderLegalAccountMock    = null;
        $this->orderMock                = null;
        $this->provinceMock             = null;
        $this->cityMock                 = null;
        $this->orderLegalAccountDTOMock = null;
        $this->orderLegalAccountService = null;
    }

    public function testItCanNotStoreLegalAccountWhenOrderLegalAccountNotFound(): void
    {
        $this->orderMock->shouldReceive('getLegalAccount')
                        ->once()
                        ->withNoArgs()
                        ->andReturnNull();

        $this->expectException(StoreOrderLegalAccountException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('There is a problem in storing order legal account!');

        $this->orderLegalAccountService->store($this->orderMock, $this->orderLegalAccountDTOMock);
    }

    public function testItCanNotStoreLegalAccountWhenOrderStatusIsDelivered(): void
    {
        $this->orderMock->shouldReceive('getLegalAccount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->orderLegalAccountMock);
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::DELIVERED);

        $this->expectException(StoreOrderLegalAccountException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('There is a problem in storing order legal account!');

        $this->orderLegalAccountService->store($this->orderMock, $this->orderLegalAccountDTOMock);
    }

    public function testItCanStoreLegalAccount(): void
    {
        $this->orderLegalAccountDTOMock->shouldReceive('getProvince')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->provinceMock);
        $this->orderLegalAccountDTOMock->shouldReceive('getCity')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->cityMock);
        $this->orderLegalAccountDTOMock->shouldReceive('getOrganizationName')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn('Lendo Cooperation');
        $this->orderLegalAccountDTOMock->shouldReceive('getEconomicCode')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn(123456789012);
        $this->orderLegalAccountDTOMock->shouldReceive('getNationalId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("04514565");
        $this->orderLegalAccountDTOMock->shouldReceive('getRegistrationId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("0415/54112");
        $this->orderLegalAccountDTOMock->shouldReceive('getPhoneNumber')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("02188203206");

        $this->orderMock->shouldReceive('getLegalAccount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->orderLegalAccountMock);
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->factoryMock->shouldReceive('getOrderLegalAccount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->orderLegalAccountMock);

        $this->orderLegalAccountMock->shouldReceive('getCustomerLegalAccount')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturn($this->customerLegalAccountMock);

        $this->orderLegalAccountMock->shouldReceive('setIsActive')
                                    ->once()
                                    ->with(false)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setOrder')
                                    ->once()
                                    ->with($this->orderMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setCustomerLegalAccount')
                                    ->once()
                                    ->with($this->customerLegalAccountMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setProvince')
                                    ->once()
                                    ->with($this->provinceMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setCity')
                                    ->once()
                                    ->with($this->cityMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setOrganizationName')
                                    ->once()
                                    ->with('Lendo Cooperation')
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setEconomicCode')
                                    ->once()
                                    ->with(123456789012)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setNationalId')
                                    ->once()
                                    ->with("04514565")
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setRegistrationId')
                                    ->once()
                                    ->with("0415/54112")
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setPhoneNumber')
                                    ->once()
                                    ->with("02188203206")
                                    ->andReturn($this->orderLegalAccountMock);

        $this->orderMock->shouldReceive('addOrderLegalAccount')
                        ->once()
                        ->with($this->orderLegalAccountMock)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->orderLegalAccountMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->orderLegalAccountService->store($this->orderMock, $this->orderLegalAccountDTOMock);

        self::assertEquals($this->orderMock, $result);
    }

    public function testItThrowsExceptionOnStoreLegalAccount(): void
    {
        $this->orderLegalAccountDTOMock->shouldReceive('getProvince')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->provinceMock);
        $this->orderLegalAccountDTOMock->shouldReceive('getCity')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn($this->cityMock);
        $this->orderLegalAccountDTOMock->shouldReceive('getOrganizationName')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn('Lendo Cooperation');
        $this->orderLegalAccountDTOMock->shouldReceive('getEconomicCode')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn(123456789012);
        $this->orderLegalAccountDTOMock->shouldReceive('getNationalId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("04514565");
        $this->orderLegalAccountDTOMock->shouldReceive('getRegistrationId')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("0415/54112");
        $this->orderLegalAccountDTOMock->shouldReceive('getPhoneNumber')
                                       ->once()
                                       ->withNoArgs()
                                       ->andReturn("02188203206");

        $this->orderMock->shouldReceive('getLegalAccount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->orderLegalAccountMock);
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->factoryMock->shouldReceive('getOrderLegalAccount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->orderLegalAccountMock);

        $this->orderLegalAccountMock->shouldReceive('getCustomerLegalAccount')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturn($this->customerLegalAccountMock);

        $this->orderLegalAccountMock->shouldReceive('setIsActive')
                                    ->once()
                                    ->with(false)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setOrder')
                                    ->once()
                                    ->with($this->orderMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setCustomerLegalAccount')
                                    ->once()
                                    ->with($this->customerLegalAccountMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setProvince')
                                    ->once()
                                    ->with($this->provinceMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setCity')
                                    ->once()
                                    ->with($this->cityMock)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setOrganizationName')
                                    ->once()
                                    ->with('Lendo Cooperation')
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setEconomicCode')
                                    ->once()
                                    ->with(123456789012)
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setNationalId')
                                    ->once()
                                    ->with("04514565")
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setRegistrationId')
                                    ->once()
                                    ->with("0415/54112")
                                    ->andReturn($this->orderLegalAccountMock);
        $this->orderLegalAccountMock->shouldReceive('setPhoneNumber')
                                    ->once()
                                    ->with("02188203206")
                                    ->andReturn($this->orderLegalAccountMock);

        $this->orderMock->shouldReceive('addOrderLegalAccount')
                        ->once()
                        ->with($this->orderLegalAccountMock)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->orderLegalAccountMock)
                 ->andThrows(new Exception());
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('rollback')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->expectException(Exception::class);

        $this->orderLegalAccountService->store($this->orderMock, $this->orderLegalAccountDTOMock);
    }
}
