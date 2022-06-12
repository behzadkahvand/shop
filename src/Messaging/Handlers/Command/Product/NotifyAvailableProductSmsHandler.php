<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\NotifyAvailableProduct;
use App\Repository\ProductNotifyRequestRepository;
use App\Service\Notification\DTOs\Customer\Product\NotifyAvailableSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Product\NotifyMe\NotifyMeService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyAvailableProductSmsHandler implements MessageHandlerInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private ProductNotifyRequestRepository $repository,
        private NotifyMeService $notifyMeService
    ) {
    }

    public function __invoke(NotifyAvailableProduct $message): void
    {
        $notifyRequests = $this->repository->findBy(['product' => $message->getProductId()]);

        foreach ($notifyRequests as $notifyRequest) {
            $product  = $notifyRequest->getProduct();
            $customer = $notifyRequest->getCustomer();

            $this->notificationService->send(
                new NotifyAvailableSmsNotificationDTO($product, $customer)
            );

            $this->notifyMeService->removeRequest(
                $customer,
                $product
            );
        }
    }
}
