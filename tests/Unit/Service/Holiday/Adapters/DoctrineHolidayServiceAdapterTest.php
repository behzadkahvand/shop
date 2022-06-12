<?php

namespace App\Tests\Unit\Service\Holiday\Adapters;

use App\Dictionary\HolidayTypeDictionary;
use App\Entity\Seller;
use App\Repository\HolidayRepository;
use App\Service\Holiday\Adapters\DoctrineHolidayServiceAdapter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class DoctrineHolidayServiceAdapterTest
 */
final class DoctrineHolidayServiceAdapterTest extends MockeryTestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(HolidayRepository::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;
    }

    public function testItCheckDateIsOpenForShipment()
    {
        $this->repository->shouldReceive('hasHolidayOfType')
                         ->once()
                         ->with(HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT, Mockery::type(\DateTimeInterface::class))
                         ->andReturnTrue();

        $service = new DoctrineHolidayServiceAdapter($this->repository);

        self::assertTrue($service->isOpenForShipment(new \DateTime()));
    }

    public function testItCheckDateIsOpenForSupply()
    {
        $this->repository->shouldReceive('hasHolidayOfType')
                         ->once()
                         ->with(HolidayTypeDictionary::HOLIDAY_TYPE_SUPPLY, Mockery::type(\DateTimeInterface::class))
                         ->andReturnTrue();

        $service = new DoctrineHolidayServiceAdapter($this->repository);

        self::assertTrue($service->isOpenForSupply(new \DateTime()));
    }

    public function testItGetsFirstOpenShipmentDateSince()
    {
        $this->repository->shouldReceive('hasHolidayOfType')
                         ->times(1)
                         ->with(HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT, Mockery::type(\DateTimeInterface::class))
                         ->andReturnTrue();

        $service  = new DoctrineHolidayServiceAdapter($this->repository);
        $dateTime = new \DateTimeImmutable();

        self::assertEquals(
            $dateTime->format('Y-m-d H:i:s'),
            $service->getFirstOpenShipmentDateSince($dateTime)->format('Y-m-d H:i:s')
        );

        $this->repository->shouldReceive('hasHolidayOfType')
                         ->twice()
                         ->with(HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT, Mockery::type(\DateTimeInterface::class))
                         ->andReturn(false, true);

        $service  = new DoctrineHolidayServiceAdapter($this->repository);
        $dateTime = new \DateTimeImmutable();

        self::assertEquals(
            $dateTime->modify('1 day')->format('Y-m-d H:i:s'),
            $service->getFirstOpenShipmentDateSince($dateTime)->format('Y-m-d H:i:s')
        );
    }

    public function testItGetsFirstOpenShipmentDateSinceConsideringTimchehHolidays(): void
    {
        $seller   = \Mockery::mock(Seller::class);

        $this->repository->shouldReceive('hasHolidayOfType')
                         ->twice()
                         ->with(
                             HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT,
                             Mockery::type(\DateTimeInterface::class),
                             $seller
                         )
                         ->andReturn(true, true);

        $this->repository->shouldReceive('hasHolidayOfType')
                         ->twice()
                         ->with(
                             HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT,
                             Mockery::type(\DateTimeInterface::class)
                         )
                         ->andReturn(false, true);

        $service  = new DoctrineHolidayServiceAdapter($this->repository);
        $dateTime = new \DateTimeImmutable();

        self::assertEquals(
            $dateTime->modify('1 day')->format('Y-m-d H:i:s'),
            $service->getFirstOpenShipmentDateSince($dateTime, $seller)->format('Y-m-d H:i:s')
        );
    }

    public function testItGetsFirstOpenSupplyDateSince()
    {
        $seller = Mockery::mock(Seller::class);

        $this->repository->shouldReceive('hasHolidayOfType')
                         ->twice()
                         ->with(
                             HolidayTypeDictionary::HOLIDAY_TYPE_SUPPLY,
                             Mockery::type(\DateTimeInterface::class),
                             $seller
                         )
                         ->andReturn(false, true);

        $service  = new DoctrineHolidayServiceAdapter($this->repository);
        $dateTime = new \DateTimeImmutable();

        self::assertEquals(
            $dateTime->modify('1 day')->format('Y-m-d H:i:s'),
            $service->getFirstOpenSupplyDateSince($dateTime, $seller)->format('Y-m-d H:i:s')
        );
    }

    public function testItGetsDriverName()
    {
        self::assertEquals('database', DoctrineHolidayServiceAdapter::getName());
    }
}
