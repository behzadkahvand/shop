<?php

namespace App\Tests\Unit\Command\Notification;

use App\Command\Notification\Customer\AbandonedCartsSmsNotificationCommand;
use App\Entity\Cart;
use App\Entity\Configuration;
use App\Repository\CartRepository;
use App\Service\AbandonedCartNotification\AbandonedCartNotificationService;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Tests\Unit\BaseUnitTestCase;
use DateTime;
use DateTimeImmutable;
use Mockery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AbandonedCartsSmsNotificationCommandTest extends BaseUnitTestCase
{
    protected static $defaultName = 'timcheh:notification:customer:abandoned-carts-sms';
    private CommandTester|null $commandTester;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|CartRepository|null $cartRepositoryMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|AbandonedCartNotificationService|null $abandonedCartNotificationServiceMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|ConfigurationServiceInterface|null $configurationServiceInterfaceMock;
    private Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface|null $configurationMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepositoryMock = Mockery::mock(CartRepository::class);
        $this->abandonedCartNotificationServiceMock = Mockery::mock(AbandonedCartNotificationService::class);
        $this->configurationServiceInterfaceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->configurationMock = Mockery::mock(Configuration::class);

        $application = new Application();
        $application->add(new AbandonedCartsSmsNotificationCommand(
            $this->cartRepositoryMock,
            $this->abandonedCartNotificationServiceMock,
            $this->configurationServiceInterfaceMock,
            true
        ));

        $command = $application->find('timcheh:notification:customer:abandoned-carts-sms');

        $this->commandTester = new CommandTester($command);
    }

    public function testItDoNothingWhenConfigNotExists(): void
    {
        $this->configurationServiceInterfaceMock->expects('findByCode')
            ->andReturnNull();

        $this->commandTester->execute([]);
    }

    public function testItDoNothingWhenConfigValueNotExists(): void
    {
        $this->configurationServiceInterfaceMock->expects('findByCode')
            ->andReturn($this->configurationMock);

        $this->configurationMock->expects('getValue')
            ->andReturnNull();

        $this->commandTester->execute([]);
    }

    public function testItDoNothingWhenCartNotExists(): void
    {
        $this->configurationServiceInterfaceMock->expects('findByCode')
            ->andReturn($this->configurationMock);

        $this->configurationMock->expects('getValue')
            ->andReturn([0, 1]);

        $this->cartRepositoryMock->expects('findAllAbandonedCartBySpecificTime')
            ->with(Mockery::type(DateTimeImmutable::class))
            ->andReturn([]);

        $this->commandTester->execute([]);
    }

    public function testItCanExecute(): void
    {
        $this->configurationServiceInterfaceMock->expects('findByCode')
            ->andReturn($this->configurationMock);

        $this->configurationMock->expects('getValue')
            ->andReturn([0, 1]);

        $cartMock = Mockery::mock(Cart::class);
        $this->cartRepositoryMock->expects('findAllAbandonedCartBySpecificTime')
            ->with(Mockery::type(DateTimeImmutable::class))
            ->andReturn([$cartMock, $cartMock]);

        $this->abandonedCartNotificationServiceMock->shouldReceive('handle')
            ->times(2)
            ->with($cartMock, [0, 1])
            ->andReturn();

        $this->commandTester->execute([]);
    }
}
