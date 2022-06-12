<?php

namespace App\Tests\Unit\Service\Auth;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\Auth\AuthService;
use App\Service\Auth\Exceptions\AuthenticationException;
use App\Service\OTP\OtpService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthServiceTest extends MockeryTestCase
{
    private const OTP = '123456';

    private const TOKEN = 'TOKEN_123';

    /**
     * @var m\Mock|CustomerRepository
     */
    private $repository;

    /**
     * @var m\Mock|OtpService
     */
    private $otpService;

    /**
     * @var m\Mock|Customer
     */
    private $user;

    /**
     * @var JWTTokenManagerInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $jwtManager;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|EventDispatcherInterface
     */
    private $dispatcher;

    private AuthService $authService;

    /**
     * @var AuthenticationSuccessHandler|m\LegacyMockInterface|m\MockInterface
     */
    private $successHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = m::mock(CustomerRepository::class);

        $this->otpService = m::mock(OtpService::class);

        $this->jwtManager = m::mock(JWTTokenManagerInterface::class);

        $this->dispatcher = m::mock(EventDispatcherInterface::class);

        $this->successHandler = m::mock(AuthenticationSuccessHandler::class);

        $this->user = new Customer();
        $this->user->setMobile('09121234567');

        $this->authService = new AuthService(
            $this->otpService,
            $this->jwtManager,
            $this->repository,
            $this->dispatcher,
            $this->successHandler
        );
    }

    protected function tearDown(): void
    {
        $this->repository = null;
        $this->otpService = null;
        $this->jwtManager = null;
        $this->dispatcher = null;
        $this->user       = null;

        unset($this->authService);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItCanLoginUserByOtpSuccessfully(): void
    {
        $this->repository->shouldReceive('findOneBy')
                         ->once()
                         ->with(['mobile' => $this->user->getMobile()])
                         ->andReturn($this->user);

        $this->otpService->shouldReceive('isOtpValid')
                         ->once()
                         ->with($this->user, self::OTP)
                         ->andReturn(true);

        $this->otpService->shouldReceive('invalidateOtp')
                         ->once()
                         ->with($this->user)
                         ->andReturn();

        $this->jwtManager->shouldReceive('create')
                         ->once()
                         ->with($this->user)
                         ->andReturn(self::TOKEN);

        $this->dispatcher->shouldReceive('dispatch')
                         ->once()
                         ->andReturn();

        $this->successHandler->shouldReceive('handleAuthenticationSuccess')
                             ->once()
                             ->andReturn(new JsonResponse());

        $response = $this->authService->loginByOtp($this->user->getMobile(), self::OTP)->toArray();

        self::assertIsArray($response);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItFailsLoginUserByOtpIfUserNotExisted(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->repository->shouldReceive('findOneBy')
                         ->once()
                         ->with(['mobile' => $this->user->getMobile()])
                         ->andReturn(null);

        $this->authService->loginByOtp($this->user->getMobile(), self::OTP);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItFailsLoginUserByOtpIfOtpCodeIsInvalid(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->repository->shouldReceive('findOneBy')
                         ->once()
                         ->with(['mobile' => $this->user->getMobile()])
                         ->andReturn($this->user);

        $this->otpService->shouldReceive('isOtpValid')
                         ->once()
                         ->with($this->user, self::OTP)
                         ->andReturn(false);

        $this->authService->loginByOtp($this->user->getMobile(), self::OTP);
    }
}
