<?php

namespace App\Messaging\Handlers\Command\Order;

use App\Messaging\Messages\Command\Order\SendOrderSurveySms;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\Customer\Order\SendOrderSurveySmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\Survey\Link\SurveyLinkGeneratorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendOrderSurveySmsHandler implements MessageHandlerInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private SurveyLinkGeneratorInterface $surveyLinkGenerator,
        private NotificationService $notificationService
    ) {
    }

    public function __invoke(SendOrderSurveySms $command): void
    {
        if (null === $order = $this->orderRepository->find($command->getOrderId())) {
            return;
        }

        $surveyLink = $this->surveyLinkGenerator->generateLink($order->getIdentifier());

        $this->notificationService->send(new SendOrderSurveySmsNotificationDTO($order->getCustomer(), $surveyLink));
    }
}
