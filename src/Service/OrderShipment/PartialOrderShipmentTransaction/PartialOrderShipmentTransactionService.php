<?php

namespace App\Service\OrderShipment\PartialOrderShipmentTransaction;

use App\Dictionary\TransactionStatus;
use App\DTO\Admin\PartialOrderShipmentTransactionData;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderShipmentRepository;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\Exceptions\OrderShipmentNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PartialOrderShipmentTransactionService
{
    public const GATEWAY_NAME = 'OrderShipmentGateway';

    protected OrderShipmentRepository $orderShipmentRepository;

    protected PartialOrderShipmentTransactionFactory $factory;

    protected EntityManagerInterface $entityManager;

    protected EventDispatcherInterface $dispatcher;

    public function __construct(
        OrderShipmentRepository $orderShipmentRepository,
        PartialOrderShipmentTransactionFactory $factory,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->orderShipmentRepository = $orderShipmentRepository;
        $this->factory                 = $factory;
        $this->entityManager           = $entityManager;
        $this->dispatcher              = $dispatcher;
    }

    public function create(int $shipmentId, PartialOrderShipmentTransactionData $transactionData): void
    {
        $this->entityManager->beginTransaction();

        try {
            $orderShipment = $this->orderShipmentRepository->findShipmentForCreateTransaction($shipmentId);

            if (!$orderShipment) {
                throw new OrderShipmentNotFoundException();
            }

            $order         = $orderShipment->getOrder();
            $orderDocument = $order->getOrderDocument();

            $transaction = $this->factory->createTransaction();

            $amount = $orderShipment->getGrandTotal() + $orderShipment->getTotalOrderItemPrices();

            $transaction->setAmount($amount)
                        ->setDocument($orderDocument)
                        ->setGateway(self::GATEWAY_NAME)
                        ->setPaidAt($transactionData->getPaidAt())
                        ->setTrackingNumber($transactionData->getTrackingNumber())
                        ->setStatus(TransactionStatus::SUCCESS);

            $this->entityManager->persist($transaction);

            $orderShipment->setTransaction($transaction);

            $this->entityManager->flush();

            $this->dispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->entityManager->close();
            $this->entityManager->rollback();

            throw $exception;
        }
    }
}
