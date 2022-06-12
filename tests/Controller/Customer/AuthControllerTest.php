<?php

namespace App\Tests\Controller\Customer;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Repository\CartRepository;
use App\Tests\Controller\BaseControllerTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class AuthControllerTest extends BaseControllerTestCase
{
    private ?Customer $user;

    private ?CacheItemPoolInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->manager->getRepository(Customer::class)->findOneBy([]);
        $this->cache = $this->getService(CacheItemPoolInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->user, $this->cache);

        parent::tearDown();
    }

    public function testItCanSendOtpCodeSuccessfully(): void
    {
        $response = $this->sendRequest(
            'POST',
            $this->route('customer.auth.otp.send'),
            ['mobile' => $this->user->getMobile()]
        );

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidationIfMobileIsIncorrect(): void
    {
        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.send'), ['mobile' => '00000']);

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidationIfMobileTypeIsNotString(): void
    {
        $mobile = [$this->user->getMobile()];

        $response = $this->sendRequest(
            'POST',
            $this->route('customer.auth.otp.send'),
            ['mobile' => $mobile]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItCanVerifyOtpCodeSuccessfully(): void
    {
        $otp = '123456';

        $cacheItem = $this->cache->getItem('OTP_' . $this->user->getMobile());
        $cacheItem->set($otp);
        $this->cache->save($cacheItem);

        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.verify'), [
            'mobile' => $this->user->getMobile(),
            'code' => $otp,
        ]);

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }


    /**
     * @throws InvalidArgumentException
     */
    public function testItTransferCartOfGuestToLoggedInUser(): void
    {
        $otp = '123456';

        $cacheItem = $this->cache->getItem('OTP_' . $this->user->getMobile());
        $cacheItem->set($otp);
        $this->cache->save($cacheItem);

        /** @var Cart $cart */
        $cart = $this->getService(CartRepository::class)->findOneBy([]);

        $cart->setCustomer(null);

        $this->manager->flush();

        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.verify'), [
            'mobile' => $this->user->getMobile(),
            'code' => $otp,
        ], [], ['HTTP_X-Cart' => $cart->getId()]);

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidationIfOtpIsTypeIsNotString(): void
    {
        $otp = ['123456'];

        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.verify'), [
            'mobile' => $this->user->getMobile(),
            'code' => $otp,
        ]);

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testItFailsIfOtpIsIncorrect(): void
    {
        $response = $this->sendRequest('POST', $this->route('customer.auth.otp.verify'), [
            'mobile' => $this->user->getMobile(),
            'code' => '123456',
        ]);

        self::assertEquals(401, $response->getResponse()->getStatusCode());
    }

    public function testItCanGetUserProfileByToken(): void
    {
        $response = $this->loginAs($this->user)->sendRequest('GET', $this->route('customer.auth.profile'));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKeys([
            'shebaNumber',
            'cardNumber'
        ], $results['account']);

        self::assertEquals($this->user->getName(), $results['name']);
        self::assertEquals($this->user->getFamily(), $results['family']);
        self::assertEquals($this->user->getNationalNumber(), $results['nationalNumber']);
        self::assertEquals($this->user->getPervasiveCode(), $results['pervasiveCode']);
        self::assertEquals($this->user->isProfileCompleted(), $results['isProfileCompleted']);
        self::assertEquals($this->user->getIsForeigner(), $results['isForeigner']);
        self::assertEquals($this->user->isProfileLegal(), $results['isProfileLegal']);
    }
}
