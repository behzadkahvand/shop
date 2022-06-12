<?php

namespace App\Tests\Unit\Service\Order\ApologyDelayWaitForSupply;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderNote;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Entity\ShippingPeriod;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSupplyShipmentSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\ApologyDelayWaitForSupply\ApologyDelayShipmentExpressOrdersService;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\PromotionCouponService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Security;

class ApologyDelayShipmentExpressOrdersServiceTest extends MockeryTestCase
{
    private ?ApologyDelayShipmentExpressOrdersService $apologyDelayShipmentExpressOrderService;

    private ?NotificationService $notificationServiceMock;

    private ?OrderRepository $orderRepositoryMock;

    private ?CustomerRepository $customerRepositoryMock;

    private ?PromotionCouponService $promotionCouponServiceMock;

    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|Security|null $security;

    protected function setUp(): void
    {
        $this->notificationServiceMock    = Mockery::mock(NotificationService::class);
        $this->orderRepositoryMock        = Mockery::mock(OrderRepository::class);
        $this->customerRepositoryMock     = Mockery::mock(CustomerRepository::class);
        $this->promotionCouponServiceMock = Mockery::mock(PromotionCouponService::class);
        $this->entityManagerMock          = Mockery::mock(EntityManagerInterface::class);
        $this->security                   = Mockery::mock(Security::class);

        $this->apologyDelayShipmentExpressOrderService = new ApologyDelayShipmentExpressOrdersService(
            $this->notificationServiceMock,
            $this->orderRepositoryMock,
            $this->customerRepositoryMock,
            $this->promotionCouponServiceMock,
            $this->entityManagerMock,
            $this->security
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->notificationServiceMock                 = null;
        $this->orderRepositoryMock                     = null;
        $this->customerRepositoryMock                  = null;
        $this->promotionCouponServiceMock              = null;
        $this->entityManagerMock                       = null;
        $this->apologyDelayShipmentExpressOrderService = null;
        $this->security                                = null;

        parent::tearDown();
    }

    public function testSendSuccessNotifications(): void
    {
        $promotionMock = Mockery::mock(Promotion::class);
        $promotionMock->shouldReceive('getName')
                      ->withNoArgs()
                      ->once()
                      ->andReturn("test");
        $deliveryDate   = Mockery::mock(DateTime::class);
        $shippingPeriod = Mockery::mock(ShippingPeriod::class);

        $orders = [
            [
                'orderId'    => 1,
                'identifier' => 1,
                'grandTotalItems' => 298000,
                'customerId' => 5,
                'mobile'     => '09121111111',
                'name'       => 'tester1',
                'customerFullName' => 'full name'
            ],
            [
                'orderId'    => 2,
                'identifier' => 2,
                'grandTotalItems' => 40000,
                'customerId' => 2,
                'mobile'     => '09122222222',
                'name'       => 'tester2',
                'customerFullName' => 'full name'
            ],
        ];
        $this->orderRepositoryMock->shouldReceive('getExpressOrdersWithDelayInShipmentOnSpecificDay')
                                  ->with($deliveryDate, $shippingPeriod)
                                  ->once()
                                  ->andReturn($orders);

        $this->notificationServiceMock->shouldReceive('send')
                                      ->with(Mockery::type(WaitingForSupplyShipmentSmsNotificationDTO::class))
                                      ->once()
                                      ->andReturn();

        $customerMock = Mockery::mock(Customer::class);
        $customerMock->shouldReceive('getId')
                     ->withNoArgs()
                     ->once()
                     ->andReturn(5);
        $this->customerRepositoryMock->shouldReceive('getCustomersByIds')
                                     ->with([5])
                                     ->once()
                                     ->andReturn([$customerMock]);

        $promotionCoupon = Mockery::mock(PromotionCoupon::class);
        $promotionCoupon->shouldReceive('getCode')
                        ->withNoArgs()
                        ->once()
                        ->andReturn("123");
        $this->promotionCouponServiceMock->shouldReceive('updateFromDTO')
                                         ->with(
                                             Mockery::type(PromotionCoupon::class),
                                             Mockery::type(PromotionCouponDTO::class)
                                         )
                                         ->once()
                                         ->andReturn($promotionCoupon);

        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('getId')
                  ->withNoArgs()
                  ->once()
                  ->andReturn(1);
        $orderMock->shouldReceive('addOrderNote')
                  ->with(Mockery::type(OrderNote::class))
                  ->once()
                  ->andReturnSelf();

        $this->orderRepositoryMock->shouldReceive('getOrdersByIds')
                                  ->with([1])
                                  ->once()
                                  ->andReturn([$orderMock]);

        $admin = Mockery::mock(Admin::class);
        $this->security->shouldReceive('getUser')
                       ->withNoArgs()
                       ->once()
                       ->andReturn($admin);

        $this->entityManagerMock->shouldReceive('persist')
                                ->with(Mockery::type(OrderNote::class))
                                ->once()
                                ->andReturn();

        $this->entityManagerMock->shouldReceive('flush')
                                ->withNoArgs()
                                ->once()
                                ->andReturn();

        $this->apologyDelayShipmentExpressOrderService->sendNotifyApologyExpressOrdersWaitForSupply(
            $promotionMock,
            $deliveryDate,
            $shippingPeriod
        );
    }
}
