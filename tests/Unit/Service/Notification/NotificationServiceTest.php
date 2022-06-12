<?php

namespace App\Tests\Unit\Service\Notification;

use App\Entity\Customer;
use App\Entity\Notification;
use App\Entity\Order;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Repository\NotificationRepository;
use App\Service\Notification\DTOs\Customer\Shipping\ExpressSentShipmentSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Tests\Unit\BaseUnitTestCase;
use Exception;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

class NotificationServiceTest extends BaseUnitTestCase
{
    public function testItCanSendNotificationSuccessfullyWhenCacheNotExist(): void
    {
        $orderIdentifier = 1;
        $customerName    = 'name';
        $customerFamily  = 'family';

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive([
            'getCode'             => ExpressSentShipmentSmsNotificationDTO::getCode(),
            'getSection'          => ExpressSentShipmentSmsNotificationDTO::getSection(),
            'getNotificationType' => ExpressSentShipmentSmsNotificationDTO::getNotificationType(),
        ])
                     ->withNoArgs()
                     ->once();

        $notification->shouldReceive(['getTemplate' => 'template'])->times(2)->withNoArgs();

        $notificationRepository = Mockery::mock(NotificationRepository::class);

        $cache     = Mockery::mock(CacheInterface::class);
        $cacheItem = Mockery::mock(ItemInterface::class);

        $cache->expects("get")
              ->andReturnUsing(function ($cacheKey, $closure) use ($notification, $notificationRepository, $cacheItem) {
                  $notificationRepository->shouldReceive('findOneBy')
                                         ->once()
                                         ->with([
                                             'code'             => ExpressSentShipmentSmsNotificationDTO::getCode(),
                                             'section'          => ExpressSentShipmentSmsNotificationDTO::getSection(),
                                             'notificationType' => ExpressSentShipmentSmsNotificationDTO::getNotificationType(),
                                         ])
                                         ->andReturn($notification);

                  $cacheItem->expects("expiresAfter")->with(8 * 60 * 60)->andReturn();

                  return $closure($cacheItem);
              });

        $realLoader = Mockery::mock(LoaderInterface::class);

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('getLoader')->once()->withNoArgs()->andReturn($realLoader);
        $templateEngine->shouldReceive('setLoader')->once()->with(Mockery::type(ArrayLoader::class))->andReturn();
        $templateEngine->shouldReceive('setLoader')->once()->with($realLoader)->andReturn();
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'orderId' => $orderIdentifier,
                           'name'    => $customerName,
                           'family'  => $customerFamily,
                       ])
                       ->andReturn('rendered template');

        $messenger = Mockery::mock(MessageBusInterface::class);
        $messenger->shouldReceive('dispatch')
                  ->once()
                  ->with(Mockery::type(AsyncMessage::class))
                  ->andReturn(new Envelope(new stdClass()));

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive([
            'getName'     => $customerName,
            'getFamily'   => $customerFamily,
            'getMobile'   => '09123456789',
            'getFullName' => 'full name',
            'getId'       => 1,
        ])
                 ->once()
                 ->withNoArgs();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getCustomer' => $customer, 'getIdentifier' => $orderIdentifier])
              ->once()
              ->withNoArgs();

        $notificationService = new  NotificationService(
            $notificationRepository,
            $templateEngine,
            $messenger,
            $cache,
            28800
        );

        $notificationService->send(new ExpressSentShipmentSmsNotificationDTO($order));
    }

    public function testItCanSendNotificationWhenCacheExist(): void
    {
        $orderIdentifier = 1;
        $customerName    = 'name';
        $customerFamily  = 'family';

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive([
            'getCode'             => ExpressSentShipmentSmsNotificationDTO::getCode(),
            'getSection'          => ExpressSentShipmentSmsNotificationDTO::getSection(),
            'getNotificationType' => ExpressSentShipmentSmsNotificationDTO::getNotificationType(),
        ])
                     ->withNoArgs()
                     ->once();

        $notification->shouldReceive(['getTemplate' => 'template'])->times(2)->withNoArgs();

        $notificationRepository = Mockery::mock(NotificationRepository::class);

        $cache     = Mockery::mock(CacheInterface::class);
        $cacheItem = Mockery::mock(ItemInterface::class);

        $cache->expects("get")->with(
            "cache_notification_code_CUSTOMER_EXPRESS_SHIPPING_section_OrderShipment_type_SMS",
            Mockery::type('Closure')
        )->andReturn($notification);

        $realLoader = Mockery::mock(LoaderInterface::class);

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('getLoader')->once()->withNoArgs()->andReturn($realLoader);
        $templateEngine->shouldReceive('setLoader')->once()->with(Mockery::type(ArrayLoader::class))->andReturn();
        $templateEngine->shouldReceive('setLoader')->once()->with($realLoader)->andReturn();
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'orderId' => $orderIdentifier,
                           'name'    => $customerName,
                           'family'  => $customerFamily,
                       ])
                       ->andReturn('rendered template');

        $messenger = Mockery::mock(MessageBusInterface::class);
        $messenger->shouldReceive('dispatch')
                  ->once()
                  ->with(Mockery::type(AsyncMessage::class))
                  ->andReturn(new Envelope(new stdClass()));

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive([
            'getName'     => $customerName,
            'getFamily'   => $customerFamily,
            'getMobile'   => '09123456789',
            'getFullName' => 'full name',
            'getId'       => 1,
        ])
                 ->once()
                 ->withNoArgs();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getCustomer' => $customer, 'getIdentifier' => $orderIdentifier])
              ->once()
              ->withNoArgs();

        $notificationService = new  NotificationService(
            $notificationRepository,
            $templateEngine,
            $messenger,
            $cache,
            28800
        );

        $notificationService->send(new ExpressSentShipmentSmsNotificationDTO($order));
    }

//    public function testExceptionWhenNotificationNotFound(): void
//    {
//        $orderIdentifier = 1;
//        $customerName    = 'name';
//        $customerFamily  = 'family';
//
//
//        $notificationRepository = Mockery::mock(NotificationRepository::class);
//
//        $cache     = Mockery::mock(CacheInterface::class);
//        $cacheItem = Mockery::mock(ItemInterface::class);
//
//        $cache->expects("get")
//              ->andReturnUsing(function ($cacheKey, $closure) use ($notificationRepository, $cacheItem) {
//                  $notificationRepository->shouldReceive('findOneBy')
//                                         ->once()
//                                         ->with([
//                                             'code'             => ExpressSentShipmentSmsNotificationDTO::getCode(),
//                                             'section'          => ExpressSentShipmentSmsNotificationDTO::getSection(),
//                                             'notificationType' => ExpressSentShipmentSmsNotificationDTO::getNotificationType(),
//                                         ])
//                                         ->andReturn(null);
//
//
//                  return $closure($cacheItem);
//              });
//
//
//        $templateEngine = Mockery::mock(Environment::class);
//
//        $messenger = Mockery::mock(MessageBusInterface::class);
//
//
//        $order = Mockery::mock(Order::class);
//
//        $notificationService = new  NotificationService(
//            $notificationRepository,
//            $templateEngine,
//            $messenger,
//            $cache,
//            28800
//        );
//
//        $this->expectException(Exception::class);
//
//        $notificationService->send(new ExpressSentShipmentSmsNotificationDTO($order));
//    }

    public function testItCanSendNotificationSuccessfullyWithDefaultTemplate(): void
    {
        $orderIdentifier = 1;
        $customerName    = 'name';
        $customerFamily  = 'family';

        $notificationRepository = Mockery::mock(NotificationRepository::class);
        $cache                  = Mockery::mock(CacheInterface::class);
        $cacheItem              = Mockery::mock(ItemInterface::class);

        $cache->expects("get")
              ->andReturnUsing(function ($cacheKey, $closure) use ($notificationRepository, $cacheItem) {
                  $notificationRepository->shouldReceive('findOneBy')
                                         ->once()
                                         ->with([
                                             'code'             => ExpressSentShipmentSmsNotificationDTO::getCode(),
                                             'section'          => ExpressSentShipmentSmsNotificationDTO::getSection(),
                                             'notificationType' => ExpressSentShipmentSmsNotificationDTO::getNotificationType(),
                                         ])
                                         ->andReturn(null);

                  $cacheItem->expects("expiresAfter")->with(8 * 60 * 60)->andReturn();

                  return $closure($cacheItem);
              });

        $realLoader = Mockery::mock(LoaderInterface::class);

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('getLoader')->once()->withNoArgs()->andReturn($realLoader);
        $templateEngine->shouldReceive('setLoader')->once()->with(Mockery::type(ArrayLoader::class))->andReturn();
        $templateEngine->shouldReceive('setLoader')->once()->with($realLoader)->andReturn();
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'orderId' => $orderIdentifier,
                           'name'    => $customerName,
                           'family'  => $customerFamily,
                       ])
                       ->andReturn('rendered template');

        $messenger = Mockery::mock(MessageBusInterface::class);
        $messenger->shouldReceive('dispatch')
                  ->once()
                  ->with(Mockery::type(AsyncMessage::class))
                  ->andReturn(new Envelope(new stdClass()));

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive([
            'getName'     => $customerName,
            'getFamily'   => $customerFamily,
            'getMobile'   => '09123456789',
            'getFullName' => 'full name',
            'getId'       => 1,
        ])
                 ->once()
                 ->withNoArgs();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getCustomer' => $customer, 'getIdentifier' => $orderIdentifier])
              ->once()
              ->withNoArgs();

        $notificationService = new NotificationService(
            $notificationRepository,
            $templateEngine,
            $messenger,
            $cache,
            28800
        );

        $notificationService->send(new ExpressSentShipmentSmsNotificationDTO($order));
    }
}
