<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Order;

use App\Entity\Customer;
use App\Entity\Order;
use App\Messaging\Handlers\Command\Order\SendOrderSurveySmsHandler;
use App\Messaging\Messages\Command\Order\SendOrderSurveySms;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\Customer\Order\SendOrderSurveySmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\Survey\Link\SurveyLinkGeneratorInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

final class SendOrderSurveySmsHandlerTest extends BaseUnitTestCase
{
    public function testItDoNothingIfOrderWithGivenIdIsNotFound(): void
    {
        $orderId = 1;

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive(['find' => null])->once()->with($orderId);

        $surveyLinkGenerator = Mockery::mock(SurveyLinkGeneratorInterface::class);
        $surveyLinkGenerator->shouldNotReceive('generateLink');

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $handler = new SendOrderSurveySmsHandler(
            $orderRepository,
            $surveyLinkGenerator,
            $notificationService
        );

        $handler(new SendOrderSurveySms($orderId));
    }

    public function testItSendOrderSurveySmsUsingNotificationService(): void
    {
        $orderId         = 1;
        $orderIdentifier = 123456;

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn($orderIdentifier);
        $order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn(Mockery::mock(Customer::class));

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive(['find' => $order])->once()->with($orderId);

        $surveyLinkGenerator = Mockery::mock(SurveyLinkGeneratorInterface::class);
        $surveyLinkGenerator->shouldReceive('generateLink')
                            ->once()
                            ->with($orderIdentifier)
                            ->andReturn('http://link.example');

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
                            ->once()
                            ->with(Mockery::type(SendOrderSurveySmsNotificationDTO::class))
                            ->andReturn();

        $handler = new SendOrderSurveySmsHandler(
            $orderRepository,
            $surveyLinkGenerator,
            $notificationService
        );

        $handler(new SendOrderSurveySms($orderId));
    }
}
