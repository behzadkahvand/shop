<?php

namespace App\Messaging\Handlers\Command\Notification;

use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\AbandonedCart;
use App\Repository\AbandonedNotificationLogRepository;
use App\Repository\CartRepository;
use App\Service\Notification\DTOs\Customer\Order\CustomerAbandonedCartSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AbandonedCartHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService,
        private CartRepository $cartRepository,
        private AbandonedNotificationLogRepository $abandonedNotificationLogRepository,
    ) {
    }

    public function __invoke(AbandonedCart $abandonedCart): void
    {
        $cartId = $abandonedCart->getCartId();
        $cart   = $this->cartRepository->findOneByCardId($cartId);

        if (!$cart) {
            $this->logger->error(sprintf('Notification will not be sent because cart %s is not available!', $cartId));
            return;
        }

        $this->sendNotification($cart->getCustomer());

        $this->updateLog($cartId);
    }

    private function sendNotification(Customer $customer): void
    {
        $this->notificationService->send(
            new CustomerAbandonedCartSmsNotificationDTO($customer)
        );
    }

    private function updateLog(string $cartId): void
    {
        $log = $this->abandonedNotificationLogRepository->findOneByCartId($cartId);
        $log->setTotalSent($log->getTotalSent() + 1);

        $this->entityManager->flush();
    }
}
