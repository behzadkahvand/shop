<?php

namespace App\Tests\Unit\Service\CustomerLegalAccount;

use App\DTO\Customer\CustomerLegalAccountData;
use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerLegalAccount;
use App\Entity\Province;
use App\Service\CustomerLegalAccount\CustomerLegalAccountFactory;
use App\Service\CustomerLegalAccount\CustomerLegalAccountService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerLegalAccountServiceTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var CustomerLegalAccountFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var Customer|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerMock;

    /**
     * @var CustomerLegalAccount|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $legalAccountMock;

    /**
     * @var Province|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $provinceMock;

    /**
     * @var City|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $cityMock;

    /**
     * @var CustomerLegalAccountData|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $customerLegalAccountDTOMock;

    protected ?CustomerLegalAccountService $customerLegalAccountService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                          = Mockery::mock(EntityManagerInterface::class);
        $this->factoryMock                 = Mockery::mock(CustomerLegalAccountFactory::class);
        $this->customerMock                = Mockery::mock(Customer::class);
        $this->legalAccountMock            = Mockery::mock(CustomerLegalAccount::class);
        $this->provinceMock                = Mockery::mock(Province::class);
        $this->cityMock                    = Mockery::mock(City::class);
        $this->customerLegalAccountDTOMock = Mockery::mock(CustomerLegalAccountData::class);

        $this->customerLegalAccountService = new CustomerLegalAccountService(
            $this->em,
            $this->factoryMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em                          = null;
        $this->factoryMock                 = null;
        $this->customerMock                = null;
        $this->legalAccountMock            = null;
        $this->provinceMock                = null;
        $this->cityMock                    = null;
        $this->customerLegalAccountDTOMock = null;
        $this->customerLegalAccountService = null;
    }

    public function testItCanCreateLegalAccount(): void
    {
        $this->customerLegalAccountDTOMock->shouldReceive('getCustomer')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->customerMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getProvince')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->provinceMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getCity')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->cityMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getOrganizationName')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn('Lendo Cooperation');
        $this->customerLegalAccountDTOMock->shouldReceive('getEconomicCode')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn(123456789012);
        $this->customerLegalAccountDTOMock->shouldReceive('getNationalId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("04514565");
        $this->customerLegalAccountDTOMock->shouldReceive('getRegistrationId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("0415/54112");
        $this->customerLegalAccountDTOMock->shouldReceive('getPhoneNumber')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("02188203206");

        $this->customerMock->shouldReceive('getLegalAccount')
                           ->once()
                           ->withNoArgs()
                           ->andReturnNull();

        $this->factoryMock->shouldReceive('getCustomerLegalAccount')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->legalAccountMock);

        $this->legalAccountMock->shouldReceive('setCustomer')
                               ->once()
                               ->with($this->customerMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setProvince')
                               ->once()
                               ->with($this->provinceMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setCity')
                               ->once()
                               ->with($this->cityMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setOrganizationName')
                               ->once()
                               ->with('Lendo Cooperation')
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setEconomicCode')
                               ->once()
                               ->with(123456789012)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setNationalId')
                               ->once()
                               ->with("04514565")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setRegistrationId')
                               ->once()
                               ->with("0415/54112")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setPhoneNumber')
                               ->once()
                               ->with("02188203206")
                               ->andReturn($this->legalAccountMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->legalAccountMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->customerLegalAccountService->store($this->customerLegalAccountDTOMock);

        self::assertEquals($this->legalAccountMock, $result);
    }

    public function testItCanUpdateLegalAccount(): void
    {
        $this->customerLegalAccountDTOMock->shouldReceive('getCustomer')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->customerMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getProvince')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->provinceMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getCity')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->cityMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getOrganizationName')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn('Lendo Cooperation');
        $this->customerLegalAccountDTOMock->shouldReceive('getEconomicCode')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn(123456789012);
        $this->customerLegalAccountDTOMock->shouldReceive('getNationalId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("04514565");
        $this->customerLegalAccountDTOMock->shouldReceive('getRegistrationId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("0415/54112");
        $this->customerLegalAccountDTOMock->shouldReceive('getPhoneNumber')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("02188203206");

        $this->customerMock->shouldReceive('getLegalAccount')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->legalAccountMock);

        $this->legalAccountMock->shouldReceive('setProvince')
                               ->once()
                               ->with($this->provinceMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setCity')
                               ->once()
                               ->with($this->cityMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setOrganizationName')
                               ->once()
                               ->with('Lendo Cooperation')
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setEconomicCode')
                               ->once()
                               ->with(123456789012)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setNationalId')
                               ->once()
                               ->with("04514565")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setRegistrationId')
                               ->once()
                               ->with("0415/54112")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setPhoneNumber')
                               ->once()
                               ->with("02188203206")
                               ->andReturn($this->legalAccountMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->legalAccountMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->customerLegalAccountService->store($this->customerLegalAccountDTOMock);

        self::assertEquals($this->legalAccountMock, $result);
    }

    public function testItThrowsExceptionOnStoreLegalAccount(): void
    {
        $this->customerLegalAccountDTOMock->shouldReceive('getCustomer')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->customerMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getProvince')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->provinceMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getCity')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn($this->cityMock);
        $this->customerLegalAccountDTOMock->shouldReceive('getOrganizationName')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn('Lendo Cooperation');
        $this->customerLegalAccountDTOMock->shouldReceive('getEconomicCode')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn(123456789012);
        $this->customerLegalAccountDTOMock->shouldReceive('getNationalId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("04514565");
        $this->customerLegalAccountDTOMock->shouldReceive('getRegistrationId')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("0415/54112");
        $this->customerLegalAccountDTOMock->shouldReceive('getPhoneNumber')
                                          ->once()
                                          ->withNoArgs()
                                          ->andReturn("02188203206");

        $this->customerMock->shouldReceive('getLegalAccount')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->legalAccountMock);

        $this->legalAccountMock->shouldReceive('setProvince')
                               ->once()
                               ->with($this->provinceMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setCity')
                               ->once()
                               ->with($this->cityMock)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setOrganizationName')
                               ->once()
                               ->with('Lendo Cooperation')
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setEconomicCode')
                               ->once()
                               ->with(123456789012)
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setNationalId')
                               ->once()
                               ->with("04514565")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setRegistrationId')
                               ->once()
                               ->with("0415/54112")
                               ->andReturn($this->legalAccountMock);
        $this->legalAccountMock->shouldReceive('setPhoneNumber')
                               ->once()
                               ->with("02188203206")
                               ->andReturn($this->legalAccountMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->legalAccountMock)
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

        $this->customerLegalAccountService->store($this->customerLegalAccountDTOMock);
    }
}
