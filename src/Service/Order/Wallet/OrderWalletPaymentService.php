<?php

namespace App\Service\Order\Wallet;

use App\Dictionary\TransactionStatus;
use App\DTO\Wallet\TransferRequest;
use App\Entity\Order;
use App\Entity\RefundDocument;
use App\Entity\Wallet;
use App\Service\Notification\DTOs\Customer\Wallet\WalletDepositNotificationDTO;
use App\Service\Notification\DTOs\Customer\Wallet\WalletWithdrawNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Payment\TransactionIdentifierService;
use DateTime;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class OrderWalletPaymentService
{
    public function __construct(
        protected EntityManagerInterface $manager,
        protected TransactionIdentifierService $identifierService,
        protected NotificationService $notificationService
    ) {
    }

    public function deposit(Order $order, TransferRequest $transferRequest): void
    {
        $amount = $transferRequest->getAmount();

        if ($amount <= 0) {
            return;
        }

        $this->manager->beginTransaction();

        try {
            $customer = $order->getCustomer();
            $wallet = $customer->getWallet();

            $this->manager->lock($wallet, LockMode::PESSIMISTIC_READ);

            $wallet->deposit($transferRequest);

            $refundDocument = $this->createRefundDocument();
            $refundDocument->setAmount($amount);
            $order->addRefundDocument($refundDocument);

            $transaction = $refundDocument->newTransaction();
            $transaction->setAmount($amount);
            $transaction->setGateway(Wallet::GATEWAY_NAME);
            $transaction->setStatus(TransactionStatus::SUCCESS);
            $transaction->setPaidAt(new DateTime());

            $this->manager->persist($refundDocument);
            $this->manager->persist($transaction);
            $this->manager->flush();

            $transaction->setIdentifier($this->identifierService->generateIdentifier($transaction));

            $this->manager->flush();

            $this->manager->commit();
        } catch (Throwable $exception) {
            $this->manager->close();
            $this->manager->rollback();
            throw $exception;
        }

        $this->notificationService->send(new WalletDepositNotificationDTO($customer, $amount));
    }

    public function withdraw(Order $order, TransferRequest $transferRequest): void
    {
        $amount = $transferRequest->getAmount();
        if ($amount <= 0) {
            return;
        }

        $this->manager->beginTransaction();

        try {
            $document = $order->getOrderDocument();
            $customer = $order->getCustomer();
            $wallet = $customer->getWallet();

            $this->manager->lock($wallet, LockMode::PESSIMISTIC_READ);

            $wallet->withdraw($transferRequest);

            $transaction = $document->newTransaction();
            $transaction->setAmount($amount);
            $transaction->setGateway(Wallet::GATEWAY_NAME);
            $transaction->setStatus(TransactionStatus::SUCCESS);
            $transaction->setPaidAt(new DateTime());

            $this->manager->persist($transaction);
            $this->manager->flush();

            $transaction->setIdentifier($this->identifierService->generateIdentifier($transaction));

            $this->manager->flush();

            $this->manager->commit();
        } catch (Throwable $exception) {
            $this->manager->close();
            $this->manager->rollback();
            throw $exception;
        }

        $this->notificationService->send(new WalletWithdrawNotificationDTO($customer, $amount));
    }

    protected function createRefundDocument(): RefundDocument
    {
        return new RefundDocument();
    }
}
