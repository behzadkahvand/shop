<?php

namespace App\Tests\Unit\Command\Notification;

use App\Command\Notification\AlertCustomerForWaitForPayOrderCommand;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\NotificationService;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;

class AlertCustomerForWaitForPayOrderCommandTest extends MockeryTestCase
{
    private CommandTester $commandTester;

    private LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    private NotificationService|LegacyMockInterface|MessageBusInterface|MockInterface|null $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->orderRepoMock       = Mockery::mock(OrderRepository::class);

        $application = new Application();
        $application->add(new AlertCustomerForWaitForPayOrderCommand(
            $this->orderRepoMock,
            $this->notificationService,
        ));

        $command             = $application->find('timcheh:job:alert-customers-wait-for-pay-orders');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->notificationService = null;
        $this->orderRepoMock       = null;

        Mockery::close();
    }

    public function testExecute(): void
    {
        $orders = [new Order()];

        $this->orderRepoMock->shouldReceive('findAllWaitForPayOrdersAfterSpecificTime')
                            ->once()
                            ->with(Mockery::type(DateTimeImmutable::class))
                            ->andReturn($orders);

        $this->notificationService->shouldReceive('send')
                                  ->once()
                                  ->with(Mockery::type(AbstractNotificationDTO::class))
                                  ->andReturn();

        $this->commandTester->execute([]);

        self::assertEquals(
            '[OK] You have successfully sent all customers that have wait for pay orders!',
            trim($this->commandTester->getDisplay())
        );
    }
}
