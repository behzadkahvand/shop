<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Customer;
use App\EventSubscriber\AuthenticationSubscriber;
use Doctrine\Common\Collections\ArrayCollection;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSubscriberTest extends TestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|UserInterface
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Mockery::mock(Customer::class);
    }

    public function testOnAuthenticationSuccessHandler()
    {
        $authenticationSubscriber = new AuthenticationSubscriber(1000, 2000);

        $this->user->shouldReceive('getId')->once()->andReturn(1);
        $this->user->shouldReceive('getName')->once()->andReturn("John");
        $this->user->shouldReceive('getFamily')->once()->andReturn("Doe");
        $this->user->shouldReceive('getAddresses')->once()->andReturn(new ArrayCollection());
        $this->user->shouldReceive('isProfileCompleted')->once()->andReturn(true);

        $event = new AuthenticationSuccessEvent([
            'token' => 'token',
            'refresh_token' => 'refreshToken',
        ], $this->user, new Response());
        $authenticationSubscriber->onAuthenticationSuccessHandler($event);

        $data = $event->getData();

        self::assertEquals('token', $data['token']);
        self::assertEquals('refreshToken', $data['refreshToken']);
        self::assertEquals('Bearer', $data['tokenType']);
        self::assertEquals(1000, $data['expireDate']);
        self::assertEquals(2000, $data['refreshTokenTtl']);

        self::assertArrayHasKey('id', $data['account']);
        self::assertArrayHasKey('name', $data['account']);
        self::assertArrayHasKey('family', $data['account']);
        self::assertArrayHasKey('addresses', $data['account']);
    }
}
