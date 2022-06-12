<?php

namespace App\Messaging\Handlers\Command\Order;

use App\Dictionary\OrderBalanceStatus;
use App\Entity\Order;
use App\Messaging\Messages\Command\Order\AddBalanceAmountToOrder;
use App\Service\Order\OrderBalanceStatus\OrderBalanceStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddBalanceAmountToOrderHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private OrderBalanceStatusService $balanceStatusService,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(AddBalanceAmountToOrder $addBalanceAmountToOrder): void
    {
        $orderId = $addBalanceAmountToOrder->getOrderId();

        $order = $this->entityManager->getReference(Order::class, $orderId);

        if (!$order) {
            $this->logger->error(sprintf('It can not add balance amount to order %d when order not exist!', $orderId));

            return;
        }

        $balanceData = $this->balanceStatusService->get($orderId);

        $ratio = in_array(
            $balanceData['balanceStatus'],
            [
                OrderBalanceStatus::BALANCE,
                OrderBalanceStatus::CREDITOR
            ],
            true
        ) ? 1 : -1;

        $balanceAmount = $ratio * $balanceData['balanceAmount'];

        /**
         * @var Order $order
         */
        $order->setBalanceAmount($balanceAmount);

        $this->entityManager->flush();
    }
}
