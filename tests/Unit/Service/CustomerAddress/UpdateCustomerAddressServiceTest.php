<?php

namespace App\Tests\Unit\Service\CustomerAddress;

use App\DTO\Customer\CustomerAddressData;
use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Province;
use App\Service\CustomerAddress\UpdateCustomerAddressService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UpdateCustomerAddressServiceTest extends MockeryTestCase
{
    private const CUSTOMER_FULL_ADDRESS = 'dummy address';
    private const POSTAL_CODE = 1156520041;
    private const NUMBER = 1;
    private const UNIT = 3;
    private const FLOOR = 5;
    private const NAME = 'john';
    private const FAMILY = 'doe';
    private const NATIONAL_CODE = '1223455';
    private const MOBILE = '0911111111';

    private Point $location;

    /**
     * @var EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $em;

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

    protected ?UpdateCustomerAddressService $updateCustomerAddress;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = new Point(51.65646, 35.1544);

        $this->em = Mockery::mock(EntityManagerInterface::class);

        $this->customerAddressDTOMock = Mockery::mock(
            CustomerAddressData::class
        );

        $this->customerAddressMock = Mockery::mock(CustomerAddress::class);

        $this->customerMock = Mockery::mock(Customer::class);

        $this->provinceMock = Mockery::mock(Province::class);

        $this->cityMock = Mockery::mock(City::class);

        $this->districtMock = Mockery::mock(District::class);

        $this->updateCustomerAddress = new UpdateCustomerAddressService(
            $this->em
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em = null;
        $this->customerAddressDTOMock = null;
        $this->customerMock = null;
        $this->customerMock = null;
        $this->provinceMock = null;
        $this->cityMock = null;
        $this->districtMock = null;
        $this->updateCustomerAddress = null;
    }

    public function testItCanUpdateCustomerAddressWhenMyAddressIsFalse(): void
    {
        $isMyAddress = false;
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);
        $this->customerAddressDTOMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerAddressDTOMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerAddressDTOMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);
        $this->customerAddressDTOMock->shouldReceive('getNationalCode')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);

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
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $result = $this->updateCustomerAddress->perform(
            $this->customerAddressMock,
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }

    public function testItCanUpdateCustomerAddressWhenMyAddressIsTrue(): void
    {
        $isMyAddress = true;
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);

        $this->customerAddressMock->shouldReceive('getCustomer')->once()->andReturn($this->customerMock);

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
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->customerMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerMock->shouldReceive('getNationalNumber')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('commit')->once()->withNoArgs();
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $result = $this->updateCustomerAddress->perform(
            $this->customerAddressMock,
            $this->customerAddressDTOMock
        );

        self::assertEquals($this->customerAddressMock, $result);
    }


    public function testItThrowsExceptionOnUpdatingCustomerAddress(): void
    {
        $isMyAddress = true;
        $this->customerAddressDTOMock->shouldReceive('getFullAddress')->once()->withNoArgs()->andReturn(self::CUSTOMER_FULL_ADDRESS);
        $this->customerAddressDTOMock->shouldReceive('getLocation')->once()->withNoArgs()->andReturn($this->location);
        $this->customerAddressDTOMock->shouldReceive('getProvince')->once()->withNoArgs()->andReturn($this->provinceMock);
        $this->customerAddressDTOMock->shouldReceive('getCity')->once()->withNoArgs()->andReturn($this->cityMock);
        $this->customerAddressDTOMock->shouldReceive('getPostalCode')->once()->withNoArgs()->andReturn(self::POSTAL_CODE);
        $this->customerAddressDTOMock->shouldReceive('getNumber')->once()->withNoArgs()->andReturn(self::NUMBER);
        $this->customerAddressDTOMock->shouldReceive('isMyAddress')->once()->withNoArgs()->andReturn($isMyAddress);
        $this->customerAddressDTOMock->shouldReceive('getUnit')->once()->withNoArgs()->andReturn(self::UNIT);
        $this->customerAddressDTOMock->shouldReceive('getFloor')->once()->withNoArgs()->andReturn(self::FLOOR);
        $this->customerAddressDTOMock->shouldReceive('getDistrict')->once()->withNoArgs()->andReturn($this->districtMock);

        $this->customerAddressMock->shouldReceive('getCustomer')->once()->andReturn($this->customerMock);

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
        $this->customerAddressMock->shouldReceive('setDistrict')->once()->with($this->districtMock)->andReturn($this->customerAddressMock);

        $this->customerMock->shouldReceive('getName')->once()->withNoArgs()->andReturn(self::NAME);
        $this->customerMock->shouldReceive('getFamily')->once()->withNoArgs()->andReturn(self::FAMILY);
        $this->customerMock->shouldReceive('getNationalNumber')->once()->withNoArgs()->andReturn(self::NATIONAL_CODE);
        $this->customerMock->shouldReceive('getMobile')->once()->withNoArgs()->andReturn(self::MOBILE);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->em->shouldReceive('close')->once()->withNoArgs();
        $this->em->shouldReceive('rollback')->once()->withNoArgs();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andThrow(new Exception());

        $this->expectException(Exception::class);

        $this->updateCustomerAddress->perform(
            $this->customerAddressMock,
            $this->customerAddressDTOMock
        );
    }
}
