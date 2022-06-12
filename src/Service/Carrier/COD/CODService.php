<?php

namespace App\Service\Carrier\COD;

use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Service\Carrier\COD\Condition\CODConditionsAggregator;
use App\Service\Carrier\Exceptions\CODPriceIsNotEquivalentToShipmentPayableException;
use App\Service\Payment\Gateways\CODGateway;
use App\Service\Payment\PaymentService;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\TransactionIdentifierService;
use Doctrine\ORM\EntityManagerInterface;

final class CODService
{
    private CODConditionsAggregator $conditions;

    private PaymentService $paymentService;

    private EntityManagerInterface $manager;

    private TransactionIdentifierService $identifierService;

    public function __construct(
        CODConditionsAggregator $conditions,
        PaymentService $paymentService,
        EntityManagerInterface $manager,
        TransactionIdentifierService $identifierService
    ) {
        $this->conditions = $conditions;
        $this->paymentService = $paymentService;
        $this->manager = $manager;
        $this->identifierService = $identifierService;
    }

    public function registerTransaction(OrderShipment $orderShipment, AbstractBankResponse $codTransactionData): void
    {
        $this->checkConditions($orderShipment, $codTransactionData);

        $transaction = $this->createTransaction($orderShipment, $codTransactionData);

        $orderShipment->setTransaction($transaction);

        $this->paymentService->verify($transaction, $codTransactionData);
    }

    private function checkConditions(OrderShipment $orderShipment, AbstractBankResponse $codTransactionData): void
    {
        if ($codTransactionData->getAmount() !== $orderShipment->getPayable()) {
            throw new CODPriceIsNotEquivalentToShipmentPayableException();
        }

        $this->conditions->apply($orderShipment);
    }

    private function createTransaction(
        OrderShipment $orderShipment,
        AbstractBankResponse $codTransactionData
    ): Transaction {
        $transaction = (new Transaction())
            ->setAmount($codTransactionData->getAmount() / 10) // Convert Rials to Toman
            ->setGateway(CODGateway::getName())
            ->setDocument($orderShipment->getOrder()->getOrderDocument())
            ->setOrderShipment($orderShipment);

        $this->manager->persist($transaction);
        $this->manager->flush();

        $transactionIdentifier = $this->identifierService->generateIdentifier($transaction);

        $transaction->setIdentifier($transactionIdentifier);

        $this->manager->flush();

        return $transaction;
    }
}
