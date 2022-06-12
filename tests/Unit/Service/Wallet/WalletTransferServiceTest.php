<?php

namespace App\Tests\Unit\Service\Wallet;

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
use App\Service\Wallet\WalletTransferService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WalletTransferServiceTest extends BaseUnitTestCase
{
    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;
    private CustomerRepository|LegacyMockInterface|MockInterface|null $customerRepo;
    private NotificationService|LegacyMockInterface|MockInterface|null $notificationService;
    private LoggerInterface|LegacyMockInterface|MockInterface|null $logger;
    private WalletTransferService|null $sut;
    private LegacyMockInterface|WalletHistoryRepository|MockInterface|null $walletHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);
        $this->customerRepo = Mockery::mock(CustomerRepository::class);
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->walletHistoryRepository = Mockery::mock(WalletHistoryRepository::class);

        $this->sut = new WalletTransferService(
            $this->customerRepo,
            $this->em,
            $this->logger,
            $this->notificationService,
            $this->walletHistoryRepository
        );
    }

    public function testDepositWhenReferenceIdIsUsedBefore(): void
    {
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';
        $customer = new Customer();
        $customer->setWallet(new Wallet());

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::DEPOSIT])
            ->andReturn(new WalletHistory());

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('reference id is used before');

        $this->sut->deposit($mobile, $amount, $refId);
    }

    public function testDepositWhenCustomerExist(): void
    {
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';
        $customer = new Customer();
        $customer->setWallet(new Wallet());

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::DEPOSIT])
            ->andReturnNull();
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andReturn($customer);
        $this->em->shouldReceive('lock')->once()->with(Wallet::class, LockMode::PESSIMISTIC_READ)->andReturnNull();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        $this->notificationService->shouldReceive('send')->once()->with(WalletDepositNotificationDTO::class);

        $wallet = $this->sut->deposit($mobile, $amount, $refId);

        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertEquals($amount, $wallet->getBalance());
        $history = $wallet->getHistories()->first();
        assert($history instanceof WalletHistory);
        self::assertEquals($refId, $history->getReferenceId());
        self::assertEquals(TransferReason::LENDO_CHARGE, $history->getReason());
    }

    public function testDepositWhenCustomerDoesNotExist(): void
    {
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::DEPOSIT])
            ->andReturnNull();
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andReturnNull();
        $this->em->shouldReceive('lock')->once()->with(Wallet::class, LockMode::PESSIMISTIC_READ)->andReturnNull();
        $this->em->shouldReceive('persist')->once()->with(Customer::class)->andReturnNull();
        $this->em->shouldReceive('flush')->twice()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        $this->notificationService->shouldReceive('send')->once()->with(WalletDepositNotificationDTO::class);

        $wallet = $this->sut->deposit($mobile, $amount, $refId);
        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertEquals($amount, $wallet->getBalance());
        $history = $wallet->getHistories()->first();
        assert($history instanceof WalletHistory);
        self::assertEquals($refId, $history->getReferenceId());
        self::assertEquals(TransferReason::LENDO_CHARGE, $history->getReason());
    }

    public function testDepositWhenExceptionIsThrown(): void
    {
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::DEPOSIT])
            ->andReturnNull();
        $exception = new Exception('dummy message');
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andThrow($exception);
        $this->logger
            ->shouldReceive('critical')
            ->once()
            ->with(
                'Error in charging user wallet',
                [
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'referenceId' => $refId,
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            )
        ->andReturnNull();

        $this->expectException(Exception::class);

        $this->sut->deposit($mobile, $amount, $refId);
    }

    public function testWithdrawWhenCustomerExist(): void
    {
        $initialAmount = 5000;
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';
        $customer = new Customer();
        $wallet = new Wallet();
        $wallet->deposit(new TransferRequest($initialAmount, 'dummy reason'));
        $customer->setWallet($wallet);

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::WITHDRAW])
            ->andReturnNull();
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andReturn($customer);
        $this->em->shouldReceive('lock')->once()->with(Wallet::class, LockMode::PESSIMISTIC_READ)->andReturnNull();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        $this->notificationService->shouldReceive('send')->once()->with(WalletWithdrawNotificationDTO::class);

        $returnedWallet = $this->sut->withdraw($mobile, $amount, $refId);

        self::assertInstanceOf(Wallet::class, $returnedWallet);
        self::assertEquals($initialAmount - $amount, $returnedWallet->getBalance());
        $history = $returnedWallet->getHistories()[1];
        assert($history instanceof WalletHistory);
        self::assertEquals(TransferReason::LENDO_DISCHARGE, $history->getReason());
    }

    public function testWithdrawWhenCustomerDoesNotExist(): void
    {
        $amount = 1000;
        $mobile = '091111111';
        $refId = 1;

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::WITHDRAW])
            ->andReturnNull();
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andReturnNull();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();
        $this->logger
            ->shouldReceive('critical')
            ->once()
            ->andReturnNull();

        $this->expectException(NotFoundHttpException::class);

        $this->sut->withdraw($mobile, $amount, $refId);
    }

    public function testWithdrawWhenExceptionIsThrown(): void
    {
        $amount = 1000;
        $mobile = '091111111';
        $refId = 1;

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::WITHDRAW])
            ->andReturnNull();
        $exception = new Exception('dummy message');
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->customerRepo->shouldReceive('findOneBy')->once()->with(['mobile' => $mobile])->andThrow($exception);
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturnNull();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();
        $this->logger
            ->shouldReceive('critical')
            ->once()
            ->with(
                'Error in discharging user wallet',
                [
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            )
            ->andReturnNull();

        $this->expectException(Exception::class);

        $this->sut->withdraw($mobile, $amount, $refId);
    }

    public function testWithdrawWhenReferenceIdIsUsedBefore(): void
    {
        $amount = 1000;
        $refId = 1;
        $mobile = '091111111';
        $customer = new Customer();
        $customer->setWallet(new Wallet());

        $this->walletHistoryRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['referenceId' => $refId, 'type' => WalletHistory::WITHDRAW])
            ->andReturn(new WalletHistory());

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('reference id is used before');

        $this->sut->withdraw($mobile, $amount, $refId);
    }
}
