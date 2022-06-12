<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Notification;

use App\Entity\AbandonedNotificationLog;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Messaging\Handlers\Command\Notification\AbandonedCartHandler;
use App\Messaging\Messages\Command\Notification\AbandonedCart;
use App\Repository\AbandonedNotificationLogRepository;
use App\Repository\CartRepository;
use App\Service\Notification\NotificationService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Psr\Log\LoggerInterface;

class AbandonedCartHandlerTest extends BaseUnitTestCase
{
    private ?AbandonedCartHandler $abandonedCartHandler;

    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerMock;

    private NotificationService|Mockery\LegacyMockInterface|Mockery\MockInterface|null $notificationServiceMock;

    private AbandonedNotificationLog|Mockery\LegacyMockInterface|Mockery\MockInterface|null $abandonedNotificationLogMock;

    private ?string $cartId;

    private LoggerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface|null $loggerMock;

    private Cart|Mockery\LegacyMockInterface|Mockery\MockInterface|null $cartMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|Customer|null $customerMock;

    private AbandonedNotificationLogRepository|Mockery\LegacyMockInterface|Mockery\MockInterface|null $abandonedNotificationLogRepositoryMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|CartRepository|null $cartRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock                      = Mockery::mock(EntityManagerInterface::class);
        $this->notificationServiceMock                = Mockery::mock(NotificationService::class);
        $this->cartRepositoryMock                     = Mockery::mock(CartRepository::class);
        $this->abandonedNotificationLogRepositoryMock = Mockery::mock(AbandonedNotificationLogRepository::class);
        $this->abandonedNotificationLogMock           = Mockery::mock(AbandonedNotificationLog::class);
        $this->loggerMock                             = Mockery::mock(LoggerInterface::class);
        $this->cartMock                               = Mockery::mock(Cart::class);
        $this->customerMock                           = Mockery::mock(Customer::class);

        $this->abandonedCartHandler = new AbandonedCartHandler(
            $this->entityManagerMock,
            $this->notificationServiceMock,
            $this->cartRepositoryMock,
            $this->abandonedNotificationLogRepositoryMock
        );

        $this->cartId = "cartId";
    }

    public function testItDoNothingWhenCartNotFound(): void
    {
        $abandonedCart = new AbandonedCart($this->cartId);

        $this->cartRepositoryMock->expects('findOneByCardId')
                                 ->andReturnNull();

        $this->abandonedCartHandler->setLogger($this->loggerMock);

        $this->loggerMock->expects('error')
                         ->with(sprintf('Notification will not be sent because cart %s is not available!', $this->cartId))
                         ->andReturn();

        $this->abandonedCartHandler->__invoke($abandonedCart);
    }

    public function testItShouldUpdateLogAndSendNotification(): void
    {
        $abandonedCart = new AbandonedCart($this->cartId);

        $this->cartRepositoryMock->expects('findOneByCardId')
                                 ->andReturn($this->cartMock);

        $this->cartMock->expects('getCustomer')
                       ->andReturn($this->customerMock);

        $this->abandonedNotificationLogRepositoryMock->expects('findOneByCartId')
                                                     ->with($this->cartId)
                                                     ->andReturn($this->abandonedNotificationLogMock);

        $this->notificationServiceMock->expects('send')
                                      ->andReturnNull();

        $this->abandonedNotificationLogMock->expects('getTotalSent')
                                           ->andReturn(0);

        $this->abandonedNotificationLogMock->expects('setTotalSent')
                                           ->with(1)
                                           ->andReturnSelf();

        $this->entityManagerMock->expects('flush')
                                ->andReturnNull();

        $this->abandonedCartHandler->__invoke($abandonedCart);
    }
}
