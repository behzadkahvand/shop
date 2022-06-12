<?php

namespace App\Tests\Unit\Service\Holiday\Adapters;

use App\Service\Holiday\Adapters\FridayHolidayServiceAdapter;
use App\Service\Holiday\HolidayServiceInterface;
use App\Tests\Unit\BaseUnitTestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

final class FridayHolidayServiceAdapterTest extends BaseUnitTestCase
{
    protected HolidayServiceInterface|LegacyMockInterface|MockInterface|null $innerHolidayMock;

    protected ?FridayHolidayServiceAdapter $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->innerHolidayMock = Mockery::mock(HolidayServiceInterface::class);
        $this->sut              = new FridayHolidayServiceAdapter($this->innerHolidayMock);
    }

    public function testItCheckFridaysForShipmentHolidays(): void
    {
        $datetime = new DateTimeImmutable('2020-07-31 09:00:00');

        $this->innerHolidayMock->expects('isOpenForShipment')
                               ->with(Mockery::type(DateTimeInterface::class))
                               ->andReturnTrue();

        self::assertFalse($this->sut->isOpenForShipment($datetime));
        self::assertTrue($this->sut->isOpenForShipment($datetime->modify('1 day')));
    }

    public function testItCheckFridaysForSupplyHolidays(): void
    {
        $datetime = new DateTimeImmutable('2020-07-31 09:00:00');

        $this->innerHolidayMock->expects('isOpenForSupply')
                               ->with(Mockery::type(DateTimeInterface::class))
                               ->andReturnTrue();

        self::assertFalse($this->sut->isOpenForSupply($datetime));
        self::assertTrue($this->sut->isOpenForSupply($datetime->modify('1 day')));
    }

    public function testItGetsFirstOpenShipmentDateSince(): void
    {
        $datetime = new DateTimeImmutable('2020-07-31 09:00:00');

        $this->innerHolidayMock->expects('isOpenForShipment')
                               ->with(Mockery::type(DateTimeInterface::class))
                               ->andReturnTrue();
        self::assertEquals(
            new DateTimeImmutable('2020-08-01 09:00:00'),
            $this->sut->getFirstOpenShipmentDateSince($datetime)
        );
    }

    public function testItGetsDriverName(): void
    {
        self::assertEquals('friday', $this->sut::getName());
    }
}
