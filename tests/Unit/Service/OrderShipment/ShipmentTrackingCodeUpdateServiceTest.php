<?php

namespace App\Tests\Unit\Service\OrderShipment;

use App\Dictionary\ShipmentTrackingCodeStatus;
use App\Entity\Admin;
use App\Entity\OrderShipment;
use App\Entity\ShipmentTrackingCodeUpdate;
use App\Repository\OrderShipmentRepository;
use App\Repository\ShipmentTrackingCodeUpdateRepository;
use App\Service\File\CSV\TrackingCodeRow;
use App\Service\File\FileService;
use App\Service\OrderShipment\ShipmentTrackingCodeUpdateService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use ReflectionClass;
use Tightenco\Collect\Support\LazyCollection;

class ShipmentTrackingCodeUpdateServiceTest extends MockeryTestCase
{
    private ?FileService $fileServiceMock;

    private ?EntityManagerInterface $entityManagerMock;

    private ?OrderShipmentRepository $orderShipmentRepositoryMock;

    private ?ShipmentTrackingCodeUpdateService $shipmentTrackingCodeService;

    private ?ShipmentTrackingCodeUpdateRepository $shipmentTrackingCodeRepositoryMock;

    protected function setUp(): void
    {
        $this->shipmentTrackingCodeRepositoryMock = Mockery::mock(ShipmentTrackingCodeUpdateRepository::class);
        $this->fileServiceMock                    = Mockery::mock(FileService::class);
        $this->entityManagerMock                  = Mockery::mock(EntityManagerInterface::class);
        $this->orderShipmentRepositoryMock        = Mockery::mock(OrderShipmentRepository::class);

        $this->shipmentTrackingCodeService = Mockery::mock(
            ShipmentTrackingCodeUpdateService::class,
            [
                $this->shipmentTrackingCodeRepositoryMock,
                $this->fileServiceMock,
                $this->entityManagerMock,
                $this->orderShipmentRepositoryMock,
            ]
        )
                                                    ->makePartial()
                                                    ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->shipmentTrackingCodeRepositoryMock = null;
        $this->fileServiceMock                    = null;
        $this->entityManagerMock                  = null;
        $this->orderShipmentRepositoryMock        = null;
        $this->shipmentTrackingCodeService        = null;
        parent::tearDown();
    }

    public function testSaveUploadedSheetFile(): void
    {
        $fullFilePath = "storage/test.csv";

        $this->entityManagerMock->shouldReceive('persist')
                                ->once()
                                ->andReturn();
        $this->entityManagerMock->shouldReceive('flush')
                                ->once()
                                ->andReturn();

        $result = $this->shipmentTrackingCodeService->saveUploadedSheetFile($fullFilePath);

        self::assertInstanceOf(ShipmentTrackingCodeUpdate::class, $result);
        self::assertEquals($fullFilePath, $result->getFilePath());
    }

    public function testProcessBatchUpdateTrackingCodes(): void
    {
        $trackingCodeID = 1;

        $shipmentTrackingCodeMock = Mockery::mock(ShipmentTrackingCodeUpdate::class);
        $shipmentTrackingCodeMock->shouldReceive('setErrors')
                                 ->once()
                                 ->andReturn($shipmentTrackingCodeMock);
        $shipmentTrackingCodeMock->shouldReceive('setStatus')
                                 ->with(ShipmentTrackingCodeStatus::PROCESSED)
                                 ->once()
                                 ->andReturn($shipmentTrackingCodeMock);

        $this->shipmentTrackingCodeRepositoryMock->shouldReceive('findOneBy')
                                          ->once()
                                          ->with([
                                              'id'     => $trackingCodeID,
                                              'status' => ShipmentTrackingCodeStatus::PENDING,
                                          ])
                                          ->andReturn($shipmentTrackingCodeMock);

        $lazyCollection = Mockery::mock(LazyCollection::class);
        $lazyCollection->shouldReceive('each')
                       ->once()
                       ->andReturn($lazyCollection);

        $this->shipmentTrackingCodeService->shouldReceive('getFileRows')
                                          ->once()
                                          ->andReturn($lazyCollection);

        $this->entityManagerMock->shouldReceive('flush')
                                ->once()
                                ->andReturn();
        $this->entityManagerMock->shouldReceive('clear')
                                ->once()
                                ->andReturn();

        $this->shipmentTrackingCodeService->processBatchUpdateTrackingCodes($trackingCodeID);
    }

    public function testProcessRowWithNoError(): void
    {
        $orderShipmentMock = Mockery::mock(OrderShipment::class);
        $orderShipmentMock->shouldReceive('setTrackingCode')
                          ->with("123")
                          ->once()
                          ->andReturn($orderShipmentMock);

        $this->orderShipmentRepositoryMock->shouldReceive('findShipmentForUpdateOrderTracking')
                                          ->with("1")
                                          ->once()
                                          ->andReturn($orderShipmentMock);

        $trackingRow = TrackingCodeRow::fromString("1", "1", "123");
        $errors      = collect([]);

        $reflectionClass  = new ReflectionClass($this->shipmentTrackingCodeService);
        $reflectionMethod = $reflectionClass->getMethod('processRow');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs(
            $this->shipmentTrackingCodeService,
            [$trackingRow, $errors]
        );

        self::assertEquals(count($errors->toArray()), 0);
    }

    public function testProcessRowWithError(): void
    {
        $this->orderShipmentRepositoryMock->shouldReceive('findShipmentForUpdateOrderTracking')
                                          ->with("1")
                                          ->once()
                                          ->andReturn(null);

        $trackingRow = TrackingCodeRow::fromString("1", "1", "123");
        $errors      = collect([]);

        $reflectionClass  = new ReflectionClass($this->shipmentTrackingCodeService);
        $reflectionMethod = $reflectionClass->getMethod('processRow');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs(
            $this->shipmentTrackingCodeService,
            [$trackingRow, $errors]
        );

        self::assertEquals(count($errors->toArray()), 1);
    }

    public function testGetAdminUploadSheetResultWithErrors(): void
    {
        $admin = Mockery::mock(Admin::class);
        $admin->shouldReceive('getUsername')
                ->twice()
                ->withNoArgs()
                ->andReturn("test");

        $this->shipmentTrackingCodeRepositoryMock->shouldReceive('getAdminUploadSheetErrors')
                                                 ->once()
                                                 ->with($admin->getUsername())
                                                 ->andReturn(["error1","errors2"]);

        $result = $this->shipmentTrackingCodeService->getAdminUploadSheetResult($admin);
        self::assertFalse($result['status']);
        self::assertNotEmpty($result['errors']);
    }

    public function testGetAdminUploadSheetResultWithoutErrors(): void
    {
        $admin = Mockery::mock(Admin::class);
        $admin->shouldReceive('getUsername')
              ->twice()
              ->withNoArgs()
              ->andReturn("test");

        $this->shipmentTrackingCodeRepositoryMock->shouldReceive('getAdminUploadSheetErrors')
                                                 ->once()
                                                 ->with($admin->getUsername())
                                                 ->andReturn([]);

        $result = $this->shipmentTrackingCodeService->getAdminUploadSheetResult($admin);
        self::assertTrue($result['status']);
        self::assertEmpty($result['errors']);
    }
}
