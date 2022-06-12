<?php

namespace App\Service\Auth;

use App\Entity\Customer;
use App\Events\OTP\OtpLoginEvent;
use App\Repository\CustomerRepository;
use App\Response\Auth\OtpVerifyResponse;
use App\Service\Auth\Exceptions\AuthenticationException;
use App\Service\OTP\OtpService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AuthService
{
    protected JWTTokenManagerInterface $jwtManager;

    protected OtpService $otpService;

    protected CustomerRepository $repository;

    protected EventDispatcherInterface $dispatcher;

    private AuthenticationSuccessHandler $authenticationSuccessHandler;

    public function __construct(
        OtpService $otpService,
        JWTTokenManagerInterface $jwtManager,
        CustomerRepository $repository,
        EventDispatcherInterface $dispatcher,
        AuthenticationSuccessHandler $authenticationSuccessHandler
    ) {
        $this->otpService = $otpService;
        $this->jwtManager = $jwtManager;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function loginByOtp(string $mobile, string $otp): OtpVerifyResponse
    {
        $user = $this->findUserOrFail($mobile);

        $this->validateOtp($user, $otp);

        $this->invalidateOtp($user);

        $token = $this->generateToken($user);

        $response = OtpVerifyResponse::createResponse($this->authenticationSuccessHandler->handleAuthenticationSuccess($user, $token));

        /** @see AttachCartToUserListener */
        $this->dispatcher->dispatch(new OtpLoginEvent($user, $response), 'otp.login');

        return $response;
    }

    /**
     * @throws AuthenticationException
     */
    private function findUserOrFail(string $mobile): ?Customer
    {
        $user = $this->repository->findOneBy(['mobile' => $mobile]);

        if ($user === null) {
            throw new AuthenticationException();
        }

        return $user;
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     */
    private function validateOtp(Customer $user, string $otp): void
    {
        if (! $this->otpService->isOtpValid($user, $otp)) {
            throw new AuthenticationException();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function invalidateOtp(?Customer $user): void
    {
        $this->otpService->invalidateOtp($user);
    }

    private function generateToken(Customer $user): string
    {
        return $this->jwtManager->create($user);
    }
}
