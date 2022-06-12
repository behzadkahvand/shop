<?php

namespace App\Tests\Controller\Customer;

use App\Repository\AdminRepository;
use App\Repository\CustomerRepository;
use App\Tests\Controller\BaseControllerTestCase;
use Psr\Cache\CacheItemPoolInterface;

class RefreshTokenTest extends BaseControllerTestCase
{
    private ?object $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->getService(CacheItemPoolInterface::class);
    }

    public function testRefreshCustomerToken()
    {
        /** @var AdminRepository $adminRepository */
        $adminRepository = $this->getService(CustomerRepository::class);
        $customer = $adminRepository->findOneBy([]);

        $otp = '123456';

        $cacheItem = $this->cache->getItem('OTP_' . $customer->getMobile());
        $cacheItem->set($otp);
        $this->cache->save($cacheItem);

        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.verify'), [
            'mobile' => $customer->getMobile(),
            'code' => $otp,
        ]);

        $responseData = $this->getControllerResponse();
        $refreshToken = $responseData['results']['refreshToken'];

        $this->manager->commit();

        $client = $this->sendRequest('POST', '/auth/refresh', ['refresh_token' => $refreshToken]);

        $this->assertResponseIsSuccessful();
    }

    public function testLoginSuccessfullyAndRefreshToken()
    {
        $response = $this->sendRequest(
            'POST',
            '/seller/security/login',
            [
                'username' => 'timcheh@lendo.ir',
                'password' => '123456',
            ]
        );

        self::assertResponseIsSuccessful();
        $responseData = json_decode($response->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $responseData);
        self::assertArrayHasKey('refreshToken', $responseData);

        $this->manager->commit();

        $refreshToken = $responseData['refreshToken'];
        $response = $this->sendRequest(
            'POST',
            '/seller/auth/refresh',
            ['refresh_token' => $refreshToken]
        );

        self::assertResponseIsSuccessful();
        $responseData = json_decode($response->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $responseData['results']);
        self::assertArrayHasKey('refreshToken', $responseData['results']);
    }
}
