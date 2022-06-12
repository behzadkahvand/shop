<?php

namespace App\Service\OTP;

use App\Dictionary\NotificationCodeDictionary;
use App\Entity\Customer;
use App\Entity\Wallet;
use App\Messaging\Messages\Command\Notification\OtpSmsNotification;
use App\Repository\CustomerRepository;
use App\Service\Notification\RecipientFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;

class OtpService
{
    public const OTP_PREFIX = 'OTP_';

    public function __construct(
        protected int $expireTime,
        protected CacheItemPoolInterface $cache,
        protected MessageBusInterface $messenger,
        protected EntityManagerInterface $manager,
        protected CustomerRepository $repository,
        protected RecipientFactory $recipientFactory
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function sendOTP(string $mobile): int
    {
        $otp = $this->generateOTP();

        $message = $this->prepareMessage($otp);

        $user = $this->findOrCreateUser($mobile);

        $this->saveOTP($user, $otp);

        $this->messenger->dispatch(
            new OtpSmsNotification(
                $this->recipientFactory->make($user),
                $message,
                NotificationCodeDictionary::CUSTOMER_OTP
            )
        );

        return time() + $this->expireTime;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function isOtpValid(Customer $user, string $otp): bool
    {
        $result = $this->cache->getItem(self::OTP_PREFIX . $user->getMobile())->get();

        return $result === $otp;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidateOtp(Customer $user): void
    {
        $this->cache->deleteItem(self::OTP_PREFIX . $user->getMobile());
    }

    /**
     * @throws Exception
     */
    private function prepareMessage(string $otp): string
    {
        return 'Code: ' . $otp . PHP_EOL . 'کد ورود شما به تیمچه!';
    }

    /**
     * @throws Exception
     */
    private function generateOTP(): string
    {
        return (string) random_int(10000, 99999);
    }

    private function findOrCreateUser(string $mobile): Customer
    {
        $user = $this->repository->findOneBy(['mobile' => $mobile]);

        return $user ?? $this->createUser($mobile);
    }

    private function createUser(string $mobile): Customer
    {
        $user = new Customer();
        $user->setMobile($mobile);
        $wallet = new Wallet();
        $user->setWallet($wallet);
        $user->setIsActive(true);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function saveOTP(Customer $user, string $otp): void
    {
        $cacheItem = $this->cache->getItem(self::OTP_PREFIX . $user->getMobile());

        $cacheItem->set($otp)->expiresAfter($this->expireTime);

        $this->cache->save($cacheItem);
    }
}
