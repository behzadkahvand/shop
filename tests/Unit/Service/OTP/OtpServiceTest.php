<?php

namespace App\Tests\Unit\Service\OTP;

use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\OtpSmsNotification;
use App\Repository\CustomerRepository;
use App\Service\Notification\RecipientFactory;
use App\Service\OTP\OtpService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class OtpServiceTest extends MockeryTestCase
{
    private const OTP = '123456';

    private const OTP_EXPIRE_TIME = 600;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    private LegacyMockInterface|CustomerRepository|MockInterface|null $customerRepoMock;

    protected CacheItemPoolInterface|LegacyMockInterface|MockInterface|null $cacheMock;

    private LegacyMockInterface|MockInterface|CacheItemInterface|null $cacheItemMock;

    protected LegacyMockInterface|MessageBusInterface|MockInterface|null $messengerMock;

    private ?Customer $customer;

    private ?OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em               = Mockery::mock(EntityManagerInterface::class);
        $this->customerRepoMock = Mockery::mock(CustomerRepository::class);
        $this->cacheMock        = Mockery::mock(CacheItemPoolInterface::class);
        $this->cacheItemMock    = Mockery::mock(CacheItemInterface::class);
        $this->messengerMock    = Mockery::mock(MessageBusInterface::class);

        $this->customer = new Customer();
        $this->customer->setMobile('09121234567');

        $this->otpService = new OtpService(
            self::OTP_EXPIRE_TIME,
            $this->cacheMock,
            $this->messengerMock,
            $this->em,
            $this->customerRepoMock,
            new RecipientFactory()
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItCanSendOtpSuccessfullyIfUserIsExisted(): void
    {
        $this->customerRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['mobile' => $this->customer->getMobile()])
                               ->andReturn($this->customer);

        $this->cacheMock->shouldReceive('getItem')
                        ->once()
                        ->with(OtpService::OTP_PREFIX . $this->customer->getMobile())
                        ->andReturn($this->cacheItemMock);

        $this->cacheItemMock->shouldReceive('set')
                            ->once()
                            ->andReturn($this->cacheItemMock);

        $this->cacheItemMock->shouldReceive('expiresAfter')
                            ->once()
                            ->andReturn($this->cacheItemMock);

        $this->cacheMock->shouldReceive('save')
                        ->once()
                        ->with($this->cacheItemMock)
                        ->andReturn();

        $this->messengerMock->shouldReceive('dispatch')
                            ->once()
                            ->with(Mockery::type(OtpSmsNotification::class))
                            ->andReturn(new Envelope(new stdClass()));

        $this->otpService->sendOTP($this->customer->getMobile());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItCanSendOtpSuccessfullyIfUserIsNew(): void
    {
        $this->customerRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['mobile' => $this->customer->getMobile()])
                               ->andReturn();

        $this->em->shouldReceive('persist')
                 ->once()
                 ->andReturn();

        $this->em->shouldReceive('flush')
                 ->once()
                 ->andReturn();

        $this->cacheMock->shouldReceive('getItem')
                        ->once()
                        ->with(OtpService::OTP_PREFIX . $this->customer->getMobile())
                        ->andReturn($this->cacheItemMock);

        $this->cacheItemMock->shouldReceive('set')
                            ->once()
                            ->andReturn($this->cacheItemMock);

        $this->cacheItemMock->shouldReceive('expiresAfter')
                            ->once()
                            ->andReturn($this->cacheItemMock);

        $this->cacheMock->shouldReceive('save')
                        ->once()
                        ->with($this->cacheItemMock)
                        ->andReturn();

        $this->messengerMock->shouldReceive('dispatch')
                            ->once()
                            ->with(Mockery::type(OtpSmsNotification::class))
                            ->andReturn(new Envelope(new stdClass()));

        $this->otpService->sendOTP($this->customer->getMobile());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidateOtp(): void
    {
        $this->cacheMock->shouldReceive('getItem')
                        ->once()
                        ->with(OtpService::OTP_PREFIX . $this->customer->getMobile())
                        ->andReturn($this->cacheMock);

        $this->cacheMock->shouldReceive('get')
                        ->once()
                        ->andReturn(self::OTP);

        $isValid = $this->otpService->isOtpValid($this->customer, self::OTP);

        self::assertEquals(true, $isValid);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testInvalidateOtp(): void
    {
        $this->cacheMock->shouldReceive('deleteItem')
                        ->once()
                        ->with(OtpService::OTP_PREFIX . $this->customer->getMobile())
                        ->andReturn();

        $this->otpService->invalidateOtp($this->customer);
    }
}
