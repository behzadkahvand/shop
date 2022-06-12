<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\ExpressOrdersDelayShipmentSmsCommand;
use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Entity\Promotion;
use App\Entity\ShippingPeriod;
use App\Repository\PromotionRepository;
use App\Repository\ShippingPeriodRepository;
use App\Service\Configuration\ConfigurationService;
use App\Service\Order\ApologyDelayWaitForSupply\ApologyDelayShipmentExpressOrdersService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExpressOrdersDelayShipmentSmsCommandTest extends MockeryTestCase
{
    private LegacyMockInterface|MockInterface|ApologyDelayShipmentExpressOrdersService|null $apologyDelayShipmentExpressOrdersServiceMock;

    private LegacyMockInterface|ShippingPeriodRepository|MockInterface|null $shippingPeriodRepositoryMock;

    private CacheItemPoolInterface|LegacyMockInterface|MockInterface|null $cacheItemPoolMock;

    private LoggerInterface|LegacyMockInterface|MockInterface|null $loggerInterface;

    private CommandTester $commandTester;

    private PromotionRepository|LegacyMockInterface|MockInterface|null $promotionRepository;

    private LegacyMockInterface|MockInterface|ConfigurationService|null $configurationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apologyDelayShipmentExpressOrdersServiceMock = Mockery::mock(
            ApologyDelayShipmentExpressOrdersService::class
        );
        $this->shippingPeriodRepositoryMock                 = Mockery::mock(
            ShippingPeriodRepository::class
        );
        $this->cacheItemPoolMock                            = Mockery::mock(
            CacheItemPoolInterface::class
        );
        $this->loggerInterface                              = Mockery::mock(
            LoggerInterface::class
        );
        $this->promotionRepository                          = Mockery::mock(PromotionRepository::class);
        $this->configurationService                         = Mockery::mock(ConfigurationService::class);

        $application = new Application();
        $application->add(
            new ExpressOrdersDelayShipmentSmsCommand(
                $this->apologyDelayShipmentExpressOrdersServiceMock,
                $this->shippingPeriodRepositoryMock,
                $this->cacheItemPoolMock,
                $this->loggerInterface,
                $this->promotionRepository,
                $this->configurationService
            )
        );

        $command = $application->find('timcheh:job:sms:delay-express-orders');

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->apologyDelayShipmentExpressOrdersServiceMock = null;
        $this->shippingPeriodRepositoryMock                 = null;
        $this->cacheItemPoolMock                            = null;
        $this->loggerInterface                              = null;
        $this->promotionRepository                          = null;
        $this->configurationService                         = null;

        Mockery::close();
    }

    public function testExecuteWhenShippingPeriodNotFound(): void
    {
        $this->shippingPeriodRepositoryMock->shouldReceive('getProperPeriodGivenTime')
                                           ->once()
                                           ->andReturn(null);
        $this->loggerInterface->shouldReceive('error')
                              ->once()
                              ->andReturn(null);
        $commandResult = $this->commandTester->execute([]);

        self::assertEquals(1, $commandResult);
    }

    public function testExecuteWhenAlreadyRunJob(): void
    {
        $shippingPeriodMock = Mockery::mock(ShippingPeriod::class);
        $shippingPeriodMock->shouldReceive('getId')
                           ->withNoArgs()
                           ->once()
                           ->andReturn(1);

        $this->shippingPeriodRepositoryMock->shouldReceive('getProperPeriodGivenTime')
                                           ->once()
                                           ->andReturn($shippingPeriodMock);

        $this->cacheItemPoolMock->shouldReceive('hasItem')
                                ->once()
                                ->andReturnTrue();

        $this->loggerInterface->shouldReceive('error')
                              ->once()
                              ->andReturn(null);
        $commandResult = $this->commandTester->execute([]);

        self::assertEquals(1, $commandResult);
    }

    public function testWhenPromotionIdNoFound(): void
    {
        $shippingPeriodMock = Mockery::mock(ShippingPeriod::class);
        $shippingPeriodMock->shouldReceive('getId')
                           ->withNoArgs()
                           ->once()
                           ->andReturn(1);

        $this->shippingPeriodRepositoryMock->shouldReceive('getProperPeriodGivenTime')
                                           ->once()
                                           ->andReturn($shippingPeriodMock);

        $this->cacheItemPoolMock->shouldReceive('hasItem')
                                ->once()
                                ->andReturnFalse();

        $this->configurationService->shouldReceive('findByCode')
                                   ->with(ConfigurationCodeDictionary::WAITING_FOR_SUPPLY_ORDER_APOLOGY_PROMOTION_ID)
                                   ->once()
                                   ->andReturnNull();

        $this->loggerInterface->shouldReceive('error')
                              ->once()
                              ->andReturn(null);

        $commandResult = $this->commandTester->execute([]);

        self::assertEquals(1, $commandResult);
    }

    public function testExecuteSuccessfully(): void
    {
        $shippingPeriodMock = Mockery::mock(ShippingPeriod::class);
        $shippingPeriodMock->shouldReceive('getId')
                           ->withNoArgs()
                           ->once()
                           ->andReturn(1);

        $this->shippingPeriodRepositoryMock->shouldReceive('getProperPeriodGivenTime')
                                           ->once()
                                           ->andReturn($shippingPeriodMock);

        $this->cacheItemPoolMock->shouldReceive('hasItem')
                                ->once()
                                ->andReturnFalse();

        $cacheItemInterfaceMock = Mockery::mock(CacheItemInterface::class);
        $cacheItemInterfaceMock->shouldReceive('set')
                               ->with(['total' => 1])
                               ->once()
                               ->andReturn($cacheItemInterfaceMock);
        $cacheItemInterfaceMock->shouldReceive('expiresAfter')
                               ->once()
                               ->andReturn($cacheItemInterfaceMock);

        $this->cacheItemPoolMock->shouldReceive('getItem')
                                ->once()
                                ->andReturn($cacheItemInterfaceMock);
        $this->cacheItemPoolMock->shouldReceive('save')
                                ->with($cacheItemInterfaceMock)
                                ->once()
                                ->andReturnTrue();

        $configurationMock = Mockery::mock(Configuration::class);
        $configurationMock->shouldReceive('getValue')
            ->withNoArgs()
            ->once()
            ->andReturn(831);

        $this->configurationService->shouldReceive('findByCode')
                                   ->with(ConfigurationCodeDictionary::WAITING_FOR_SUPPLY_ORDER_APOLOGY_PROMOTION_ID)
                                   ->once()
                                   ->andReturn($configurationMock);

        $promotionMock = Mockery::mock(Promotion::class);
        $this->promotionRepository->shouldReceive('find')
                                  ->with(831)
                                  ->once()
                                  ->andReturn($promotionMock);

        $this->apologyDelayShipmentExpressOrdersServiceMock->shouldReceive('sendNotifyApologyExpressOrdersWaitForSupply')
                                                           ->once()
                                                           ->andReturn(null);

        $commandResult = $this->commandTester->execute([]);

        self::assertEquals(0, $commandResult);
    }
}
