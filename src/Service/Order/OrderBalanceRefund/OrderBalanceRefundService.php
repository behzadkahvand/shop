<?php

namespace App\Service\Order\OrderBalanceRefund;

use App\Dictionary\TransactionStatus;
use App\DTO\Admin\OrderBalanceRefundData;
use App\Entity\Order;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\Customer\Payment\RefundBalanceNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\OrderBalanceRefund\Exceptions\InvalidOrderBalanceStatusException;
use App\Service\Order\OrderBalanceRefund\Exceptions\OrderNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderBalanceRefundService
{
    public const GATEWAY_NAME = 'RefundGateway';

    protected bool $sendNotification;

    protected OrderRepository $orderRepository;

    protected EntityManagerInterface $entityManager;

    protected OrderBalanceRefundFactory $factory;

    protected NotificationService $notificationService;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        bool $sendNotification,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        OrderBalanceRefundFactory $factory,
        NotificationService $notificationService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->sendNotification    = $sendNotification;
        $this->orderRepository     = $orderRepository;
        $this->entityManager       = $entityManager;
        $this->factory             = $factory;
        $this->notificationService = $notificationService;
        $this->eventDispatcher     = $eventDispatcher;
    }

    public function add(int $orderId, OrderBalanceRefundData $refundData): void
    {
        $this->entityManager->beginTransaction();

        try {
            /** @var Order $order */
            $order = $this->orderRepository->find($orderId);

            if (!$order) {
                throw new OrderNotFoundException();
            }

            $amount = $order->getBalanceAmount();

            if ($amount <= 0) {
                throw new InvalidOrderBalanceStatusException();
            }

            if ($refundData->getForce() && $refundAmount = $refundData->getAmount()) {
                $amount = $refundAmount;
            }

            $refundDocument = $this->factory->getRefundDocument();
            $refundDocument->setOrder($order)
                           ->setAmount($amount);

            $description = $refundData->getDescription();

            if ($description) {
                $refundDocument->setDescription($description);
            }

            $this->entityManager->persist($refundDocument);

            $transaction = $this->factory->getTransaction();
            $transaction->setAmount($amount)
                        ->setDocument($refundDocument)
                        ->setGateway(self::GATEWAY_NAME)
                        ->setPaidAt($refundData->getPaidAt())
                        ->setTrackingNumber($refundData->getTrackingNumber())
                        ->setStatus(TransactionStatus::SUCCESS);

            $this->entityManager->persist($transaction);

            $order->setBalanceAmount(0);

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->close();
            $this->entityManager->rollback();

            throw $e;
        }

        if ($this->sendNotification) {
            $this->notificationService->send(new RefundBalanceNotificationDTO($order, $amount));
        }
    }
}
