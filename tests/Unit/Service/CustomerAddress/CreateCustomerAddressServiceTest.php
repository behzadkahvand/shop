<?php

namespace App\Tests\Unit\Service\CustomerAddress;

use App\DTO\Customer\CustomerAddressData;
use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Province;
use App\Service\CustomerAddress\CreateCustomerAddressService;
use App\Service\CustomerAddress\CustomerAddressFactory;
use App\Service\CustomerAddress\DefaultCustomerAddressService;
use App\Service\CustomerAddress\Exceptions\UnexpectedCustomerAddressException;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateCustomerAddressServiceTest extends MockeryTestCase
{
    private const CUSTOMER_FULL_ADDRESS = 'dummy address';
    private const POSTAL_CODE = 1156520041;
    private const NUMBER = 1;
    private const UNIT = 3;
    private const FLOOR = 5;
    private const IS_FOREIGNER = false;
    private const NAME = 'john';
    private const FAMILY = 'doe';
    private const NATIONAL_CODE = '1223455';
    private const MOBILE = '0911111111';
    private const PERVASIVE_CODE = 2;

    private Point $location;

    /**
     * @var EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $em;

    /**
     * @var CustomerAddressFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var DefaultCustomerAddressService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $defaultCustomerAddressMock;

    /**
     * @var CustomerAddressData|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $customerAddressDTOMock;

    /**
     * @var CustomerAddress|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $customerAddressMock;

    /**
     * @var Customer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $customerMock;

    /**
     * @var Province|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $provinceMock;

    /**
     * @var City|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $cityMock;

    /**
     * @var District|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $districtMock;

    protected CreateCustomerAddressService $createCustomerAddress;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = new Point(51.65646, 35.1544);

        $this->em = Mockery::mock(EntityManagerInterface::class);

        $this->factoryMock = Mockery::mock(CustomerAddressFactory::class);

        $this->defaultCustomerAddressMock = Mockery::mock(DefaultCustomerAddressService::class);

        $this->customerAddressDTOMock = Mockery::mock(CustomerAddressData::class);

        $this->customerAddressMock = Mockery::mock(CustomerAddress::class);

        $this->customerMock = Mockery::mock(Customer::class);

        $this->provinceMock = Mockery::mock(Province::class);

        $this->cityMock = Mockery::mock(City::class);

        $this->districtMock = Mockery::mock(District::class);

        $this->createCustomerAddress = new CreateCustomerAddressService(
            $this->em,
            $this->factoryMock,
            $this->defaultCustomerAddressMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em                         = null;
        $this->factoryMock                = null;
        $this->defaultCustomerAddressMock = null;
        $this->customerAddressDTOMock     = null;
        $this->customerAddressMock        = null;
        $this->customerMock               = null;
        $this->provinceMock               = null;
        $this->cityMock                   = null;
        $this->districtMock               = null;

        unset($this->createCustomerAddress);
    }

    public function testItCanCreateCustomerAddressWhenDistictIsNotSetAndMyAddressIsTrue(): void
    {
        $isMyAddress = true;
        $this->customerAddressDTOMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturnNull();
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);

        $this->factoryMock->shouldReceive('getCustomerAddress')->once()->withNoArgs()->andReturn($this->customerAddressMock);

        $this->customerAddressMock->shouldReceive('setCustomer')->once()->with($this->customerMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFullAddress')->once()->with(self::CUSTOMER_FULL_ADDRESS)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCoordinates')->once()->with($this->location)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setProvince')->once()->with($this->provinceMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCity')->once()->with($this->cityMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPostalCode')->once()->with(self::POSTAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNumber')->once()->with(self::NUMBER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setName')->once()->with(self::NAME)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFamily')->once()->with(self::FAMILY)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNationalCode')->once()->with(self::NATIONAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setMobile')->once()->with(self::MOBILE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setUnit')->once()->with(self::UNIT)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFloor')->once()->with(self::FLOOR)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setIsForeigner')->once()->with(self::IS_FOREIGNER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPervasiveCode')->once()->with(self::PERVASIVE_CODE)->andReturn($this->customerAddressMock);

        $this->customerMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerMock->shouldReceive('getNationalNumber')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerMock->shouldReceive('getIsForeigner')->once()->withNoArgs()->andReturn(self::IS_FOREIGNER);
        $this->customerMock->shouldReceive('getPervasiveCode')->once()->withNoArgs()->andReturn(self::PERVASIVE_CODE);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('persist')->once()->with($this->customerAddressMock);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddressMock
            ->shouldReceive('set')
            ->once()
            ->with($this->customerMock, $this->customerAddressMock)
            ->andReturnNull();

        $result = $this->createCustomerAddress->create(
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }

    public function testItCanCreateCustomerAddressWhenDistictIsNotSetAndMyAddressIsFalse(): void
    {
        $isMyAddress = false;

        $this->customerAddressDTOMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturnNull();
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);
        $this->customerAddressDTOMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerAddressDTOMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerAddressDTOMock->shouldReceive('getNationalCode')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerAddressDTOMock->shouldReceive('isForeigner')->once()->withNoArgs()->andReturn(self::IS_FOREIGNER);
        $this->customerAddressDTOMock->shouldReceive('getPervasiveCode')->once()->withNoArgs()->andReturn(self::PERVASIVE_CODE);

        $this->factoryMock->shouldReceive('getCustomerAddress')->once()->withNoArgs()->andReturn($this->customerAddressMock);

        $this->customerAddressMock->shouldReceive('setCustomer')->once()->with($this->customerMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFullAddress')->once()->with(self::CUSTOMER_FULL_ADDRESS)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCoordinates')->once()->with($this->location)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setProvince')->once()->with($this->provinceMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCity')->once()->with($this->cityMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPostalCode')->once()->with(self::POSTAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNumber')->once()->with(self::NUMBER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setName')->once()->with(self::NAME)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFamily')->once()->with(self::FAMILY)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNationalCode')->once()->with(self::NATIONAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setMobile')->once()->with(self::MOBILE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setUnit')->once()->with(self::UNIT)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFloor')->once()->with(self::FLOOR)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setIsForeigner')->once()->with(self::IS_FOREIGNER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPervasiveCode')->once()->with(self::PERVASIVE_CODE)->andReturn($this->customerAddressMock);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('persist')->once()->with($this->customerAddressMock);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddressMock
            ->shouldReceive('set')
            ->once()
            ->with($this->customerMock, $this->customerAddressMock)
            ->andReturnNull();

        $result = $this->createCustomerAddress->create(
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }

    public function testItCanCreateCustomerAddressWhenDistictIsSetAndMyAddressIsTrue(): void
    {
        $isMyAddress = true;
        $this->customerAddressDTOMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);

        $this->factoryMock->shouldReceive('getCustomerAddress')->once()->withNoArgs()->andReturn($this->customerAddressMock);

        $this->customerAddressMock->shouldReceive('setCustomer')->once()->with($this->customerMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFullAddress')->once()->with(self::CUSTOMER_FULL_ADDRESS)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCoordinates')->once()->with($this->location)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setProvince')->once()->with($this->provinceMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCity')->once()->with($this->cityMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPostalCode')->once()->with(self::POSTAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNumber')->once()->with(self::NUMBER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setName')->once()->with(self::NAME)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFamily')->once()->with(self::FAMILY)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNationalCode')->once()->with(self::NATIONAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setMobile')->once()->with(self::MOBILE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setUnit')->once()->with(self::UNIT)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFloor')->once()->with(self::FLOOR)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setIsForeigner')->once()->with(self::IS_FOREIGNER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPervasiveCode')->once()->with(self::PERVASIVE_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->customerMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerMock->shouldReceive('getNationalNumber')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerMock->shouldReceive('getIsForeigner')->once()->withNoArgs()->andReturn(self::IS_FOREIGNER);
        $this->customerMock->shouldReceive('getPervasiveCode')->once()->withNoArgs()->andReturn(self::PERVASIVE_CODE);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('persist')->once()->with($this->customerAddressMock);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddressMock
            ->shouldReceive('set')
            ->once()
            ->with($this->customerMock, $this->customerAddressMock)
            ->andReturnNull();

        $result = $this->createCustomerAddress->create(
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }

    public function testItCanCreateCustomerAddressWhenDistictIsSetAndMyAddressIsFalse(): void
    {
        $isMyAddress = false;

        $this->customerAddressDTOMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);
        $this->customerAddressDTOMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerAddressDTOMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerAddressDTOMock->shouldReceive('getNationalCode')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerAddressDTOMock->shouldReceive('isForeigner')->once()->withNoArgs()->andReturn(self::IS_FOREIGNER);
        $this->customerAddressDTOMock->shouldReceive('getPervasiveCode')->once()->withNoArgs()->andReturn(self::PERVASIVE_CODE);

        $this->factoryMock->shouldReceive('getCustomerAddress')->once()->withNoArgs()->andReturn($this->customerAddressMock);

        $this->customerAddressMock->shouldReceive('setCustomer')->once()->with($this->customerMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFullAddress')->once()->with(self::CUSTOMER_FULL_ADDRESS)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCoordinates')->once()->with($this->location)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setProvince')->once()->with($this->provinceMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCity')->once()->with($this->cityMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPostalCode')->once()->with(self::POSTAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNumber')->once()->with(self::NUMBER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setName')->once()->with(self::NAME)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFamily')->once()->with(self::FAMILY)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNationalCode')->once()->with(self::NATIONAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setMobile')->once()->with(self::MOBILE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setUnit')->once()->with(self::UNIT)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFloor')->once()->with(self::FLOOR)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setIsForeigner')->once()->with(self::IS_FOREIGNER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPervasiveCode')->once()->with(self::PERVASIVE_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('persist')->once()->with($this->customerAddressMock);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddressMock
            ->shouldReceive('set')
            ->once()
            ->with($this->customerMock, $this->customerAddressMock)
            ->andReturnNull();

        $result = $this->createCustomerAddress->create(
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }

    public function testItThrowsExceptionWhenItHasAnExceptionOnSettingDefaultCustomerAddress(): void
    {
        $isMyAddress = true;
        $this->customerAddressDTOMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);

        $this->factoryMock->shouldReceive('getCustomerAddress')->once()->withNoArgs()->andReturn($this->customerAddressMock);

        $this->customerAddressMock->shouldReceive('setCustomer')->once()->with($this->customerMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFullAddress')->once()->with(self::CUSTOMER_FULL_ADDRESS)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCoordinates')->once()->with($this->location)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setProvince')->once()->with($this->provinceMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setCity')->once()->with($this->cityMock)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPostalCode')->once()->with(self::POSTAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNumber')->once()->with(self::NUMBER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setName')->once()->with(self::NAME)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFamily')->once()->with(self::FAMILY)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setNationalCode')->once()->with(self::NATIONAL_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setMobile')->once()->with(self::MOBILE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setUnit')->once()->with(self::UNIT)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setFloor')->once()->with(self::FLOOR)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setIsForeigner')->once()->with(self::IS_FOREIGNER)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setPervasiveCode')->once()->with(self::PERVASIVE_CODE)->andReturn($this->customerAddressMock);
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->customerMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerMock->shouldReceive('getNationalNumber')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerMock->shouldReceive('getIsForeigner')->once()->withNoArgs()->andReturn(self::IS_FOREIGNER);
        $this->customerMock->shouldReceive('getPervasiveCode')->once()->withNoArgs()->andReturn(self::PERVASIVE_CODE);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('close')->once()->withNoArgs();
        $this->em->shouldReceive('rollback')->once()->withNoArgs();
        $this->em->shouldReceive('persist')->once()->with($this->customerAddressMock);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddressMock
            ->shouldReceive('set')
            ->once()
            ->with($this->customerMock, $this->customerAddressMock)
            ->andThrow(new UnexpectedCustomerAddressException());

        $this->expectException(UnexpectedCustomerAddressException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Customer address is unexpected!');

        $this->createCustomerAddress->create($this->customerAddressDTOMock);
    }
}
