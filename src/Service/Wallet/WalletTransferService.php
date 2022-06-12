<?php

namespace App\Service\Wallet;

use App\Dictionary\TransferReason;
use App\DTO\Wallet\TransferRequest;
use App\Entity\Customer;
use App\Entity\Wallet;
use App\Entity\WalletHistory;
use App\Exceptions\Wallet\InvalidWalletTransactionException;
use App\Repository\CustomerRepository;
use App\Repository\WalletHistoryRepository;
use App\Service\Notification\DTOs\Customer\Wallet\WalletDepositNotificationDTO;
use App\Service\Notification\DTOs\Customer\Wallet\WalletWithdrawNotificationDTO;
use App\Service\Notification\NotificationService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WalletTransferService
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        protected NotificationService $notificationService,
        protected WalletHistoryRepository $historyRepository
    ) {
    }

    /**
     * @throws InvalidWalletTransactionException
     */
    public function deposit(string $mobile, int $amount, string $referenceId): Wallet
    {
        if ($this->historyRepository->findOneBy(['referenceId' => $referenceId, 'type' => WalletHistory::DEPOSIT])) {
            throw new InvalidWalletTransactionException('reference id is used before');
        }

        $this->entityManager->beginTransaction();
        try {
            $customer = $this->customerRepository->findOneBy(['mobile' => $mobile]);

            if (!isset($customer)) {
                $wallet = new Wallet();

                $customer = new Customer();
                $customer->setMobile($mobile);
                $customer->setIsActive(true);
                $customer->setWallet($wallet);

                $this->entityManager->persist($customer);
                $this->entityManager->flush();
            }

            $wallet = $customer->getWallet();

            $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_READ);

            $wallet->deposit(
                new TransferRequest(
                    $amount,
                    TransferReason::LENDO_CHARGE,
                    $referenceId
                )
            );

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->entityManager->close();
            $this->entityManager->rollback();
            $this->logger->critical(
                'Error in charging user wallet',
                [
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'referenceId' => $referenceId,
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            );

            throw $exception;
        }

        $this->notificationService->send(new WalletDepositNotificationDTO($customer, $amount));

        return $wallet;
    }

    /**
     * @throws InvalidWalletTransactionException
     */
    public function withdraw(string $mobile, int $amount, string $referenceId): Wallet
    {
        if ($this->historyRepository->findOneBy(['referenceId' => $referenceId, 'type' => WalletHistory::WITHDRAW])) {
            throw new InvalidWalletTransactionException('reference id is used before');
        }

        $this->entityManager->beginTransaction();

        try {
            $customer = $this->customerRepository->findOneBy(['mobile' => $mobile]);

            if (!isset($customer)) {
                throw new NotFoundHttpException('User not found.');
            }

            $wallet = $customer->getWallet();

            $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_READ);

            $wallet->withdraw(
                new TransferRequest(
                    $amount,
                    TransferReason::LENDO_DISCHARGE,
                    $referenceId
                )
            );

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->entityManager->close();
            $this->entityManager->rollback();
            $this->logger->critical(
                'Error in discharging user wallet',
                [
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            );

            throw $exception;
        }

        $this->notificationService->send(new WalletWithdrawNotificationDTO($customer, $amount));

        return $wallet;
    }
}
