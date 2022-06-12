<?php

namespace App\Tests\Unit\EventSubscriber\Auth;

use App\Entity\ActivableUserInterface;
use App\EventSubscriber\Auth\AuthenticationSuccessSubscriber;
use App\Service\Auth\Exceptions\AuthenticationException;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessSubscriberTest extends BaseUnitTestCase
{
    public function testShouldThrowExceptionIfUserIsNotActive(): void
    {
        $user = Mockery::mock(ActivableUserInterface::class);
        $event = Mockery::mock(AuthenticationSuccessEvent::class);
        $event->shouldReceive('getAuthenticationToken->getUser')->andReturn($user);
        $user->shouldReceive('isActive')->once()->withNoArgs()->andReturnFalse();

        $sut = new AuthenticationSuccessSubscriber();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        $sut->onSuccess($event);
    }

    public function testShouldDoNothingIfUserIsActive(): void
    {
        $user = Mockery::mock(ActivableUserInterface::class);
        $event = Mockery::mock(AuthenticationSuccessEvent::class);
        $event->shouldReceive('getAuthenticationToken->getUser')->andReturn($user);
        $user->shouldReceive('isActive')->once()->withNoArgs()->andReturnTrue();

        $sut = new AuthenticationSuccessSubscriber();

        $sut->onSuccess($event);
    }

    public function testSubscribedEvents(): void
    {
        self::assertEquals(
            [AuthenticationSuccessEvent::class => 'onSuccess'],
            AuthenticationSuccessSubscriber::getSubscribedEvents()
        );
    }
}
