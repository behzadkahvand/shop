<?php

namespace App\Tests\Unit\Service\Zones\ZoneDetector;

use App\Entity\City;
use App\Entity\CityZone;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\DistrictZone;
use App\Entity\OrderAddress;
use App\Entity\Province;
use App\Entity\ProvinceZone;
use App\Repository\CityZoneRepository;
use App\Repository\DistrictZoneRepository;
use App\Repository\ProvinceZoneRepository;
use App\Service\Zones\ZoneDetector\Exceptions\ZoneNotFoundException;
use App\Service\Zones\ZoneDetector\ZoneDetector;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class ZoneDetectorTest extends MockeryTestCase
{
    /**
     * @var DistrictZoneRepository|\Mockery\MockInterface
     */
    private $districtZoneRepo;

    /**
     * @var CityZoneRepository|\Mockery\MockInterface
     */
    private $cityZoneRepo;

    /**
     * @var ProvinceZoneRepository|\Mockery\MockInterface
     */
    private $provinceZoneRepo;

    /**
     * @var EntityManagerInterface|\Mockery\MockInterface
     */
    private $em;

    protected function setUp(): void
    {
        $this->districtZoneRepo = \Mockery::mock(DistrictZoneRepository::class);
        $this->cityZoneRepo     = \Mockery::mock(CityZoneRepository::class);
        $this->provinceZoneRepo = \Mockery::mock(ProvinceZoneRepository::class);
        $this->em               = \Mockery::mock(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        $this->districtZoneRepo = null;
        $this->cityZoneRepo = null;
        $this->provinceZoneRepo = null;
        $this->em = null;
    }

    public function testItThrowExceptionOnGetZoneByCustomerAddressIfNoZoneIsFound()
    {
        $district = new District();
        $city     = new City();
        $province = new Province();

        $address = new CustomerAddress();
        $address->setDistrict($district)->setCity($city)->setProvince($province);

        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturnNull();

        $this->cityZoneRepo->shouldReceive('findOneByCity')
                           ->once()
                           ->with($city)
                           ->andReturnNull();

        $this->provinceZoneRepo->shouldReceive('findOneByProvince')
                               ->once()
                               ->with($province)
                               ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(CityZone::class)
                 ->andReturn($this->cityZoneRepo);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(ProvinceZone::class)
                 ->andReturn($this->provinceZoneRepo);

        $zoneDetector = new ZoneDetector($this->em);

        $this->expectException(ZoneNotFoundException::class);

        $zoneDetector->getZoneForCustomerAddress($address);
    }

    public function testItGetsZoneByCustomerAddress()
    {
        $district = new District();
        $city     = new City();
        $province = new Province();

        $address = new CustomerAddress();
        $address->setDistrict($district)->setCity($city)->setProvince($province);

        $zoneDetector = new ZoneDetector($this->em);

        $districtZone = \Mockery::mock(DistrictZone::class);
        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturn($districtZone);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        self::assertSame($districtZone, $zoneDetector->getZoneForCustomerAddress($address));

        $this->em = \Mockery::mock(EntityManagerInterface::class);

        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        $cityZone = \Mockery::mock(CityZone::class);
        $this->cityZoneRepo->shouldReceive('findOneByCity')
                           ->once()
                           ->with($city)
                           ->andReturn($cityZone);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(CityZone::class)
                 ->andReturn($this->cityZoneRepo);

        $zoneDetector = new ZoneDetector($this->em);

        self::assertSame($cityZone, $zoneDetector->getZoneForCustomerAddress($address));

        $this->em = \Mockery::mock(EntityManagerInterface::class);

        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        $this->cityZoneRepo->shouldReceive('findOneByCity')
                           ->once()
                           ->with($city)
                           ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(CityZone::class)
                 ->andReturn($this->cityZoneRepo);

        $provinceZone = \Mockery::mock(ProvinceZone::class);
        $this->provinceZoneRepo->shouldReceive('findOneByProvince')
                               ->once()
                               ->with($province)
                               ->andReturn($provinceZone);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(ProvinceZone::class)
                 ->andReturn($this->provinceZoneRepo);

        $zoneDetector = new ZoneDetector($this->em);

        self::assertSame($provinceZone, $zoneDetector->getZoneForCustomerAddress($address));
    }

    public function testItThrowExceptionOnGetZoneByOrderAddressIfNoZoneIsFound()
    {
        $district = new District();
        $city     = new City();

        $address = new OrderAddress();
        $address->setDistrict($district)->setCity($city);

        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturnNull();

        $this->cityZoneRepo->shouldReceive('findOneByCity')
                           ->once()
                           ->with($city)
                           ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(CityZone::class)
                 ->andReturn($this->cityZoneRepo);

        $zoneDetector = new ZoneDetector($this->em);

        $this->expectException(ZoneNotFoundException::class);

        $zoneDetector->getZoneForOrderAddress($address);
    }

    public function testItGetsZoneByOrderAddress()
    {
        $district = new District();
        $city     = new City();

        $address = new OrderAddress();
        $address->setDistrict($district)->setCity($city);

        $zoneDetector = new ZoneDetector($this->em);

        $districtZone = \Mockery::mock(DistrictZone::class);
        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturn($districtZone);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        self::assertSame($districtZone, $zoneDetector->getZoneForOrderAddress($address));

        $this->em = \Mockery::mock(EntityManagerInterface::class);

        $this->districtZoneRepo->shouldReceive('findOneByDistrict')
                               ->once()
                               ->with($district)
                               ->andReturnNull();

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(DistrictZone::class)
                 ->andReturn($this->districtZoneRepo);

        $cityZone = \Mockery::mock(CityZone::class);
        $this->cityZoneRepo->shouldReceive('findOneByCity')
                           ->once()
                           ->with($city)
                           ->andReturn($cityZone);

        $this->em->shouldReceive('getRepository')
                 ->once()
                 ->with(CityZone::class)
                 ->andReturn($this->cityZoneRepo);

        $zoneDetector = new ZoneDetector($this->em);

        self::assertSame($cityZone, $zoneDetector->getZoneForOrderAddress($address));
    }
}
