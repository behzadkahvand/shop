<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Controller\Lendo\WalletController;
use App\Controller\Monitoring\HealthCheckController;
use App\EventSubscriber\TokenSubscriber;
use App\Tests\Unit\BaseUnitTestCase;
use Controller;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class TokenSubscriberTest extends BaseUnitTestCase
{
    public function testShouldReturnCorrectSubscribedEvents(): void
    {
        $expected = [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];

        self::assertEquals($expected, TokenSubscriber::getSubscribedEvents());
    }

    /** @dataProvider controllerProvider */
    public function testShouldThrowExceptionIfRequestDoesNotHaveAValidToken(
        array $tokens,
        string $requestTokenKey,
        string $expectedToken,
        MockInterface $controller
    ): void {
        $request = Mockery::mock(Request::class);
        $event = new ControllerEvent(
            Mockery::mock(KernelInterface::class),
            [$controller, ''],
            $request,
            1
        );
        $headers = Mockery::mock(HeaderBag::class);
        $headers->shouldReceive('get')->once()->with($requestTokenKey)->andReturn('invalid-token');
        $request->headers = $headers;

        $sut = new TokenSubscriber($tokens);

        $this->expectException(AccessDeniedHttpException::class);

        $sut->onKernelController($event);
    }

    /** @dataProvider controllerProvider */
    public function testShouldNotThrowExceptionIfRequestHasAValidToken(
        array $tokens,
        string $requestTokenKey,
        string $expectedToken,
        MockInterface $controller
    ): void {
        $request = Mockery::mock(Request::class);
        $event = new ControllerEvent(
            Mockery::mock(KernelInterface::class),
            [$controller, ''],
            $request,
            1
        );
        $headers = Mockery::mock(HeaderBag::class);
        $headers->shouldReceive('get')->once()->with($requestTokenKey)->andReturn($expectedToken);
        $request->headers = $headers;

        $sut = new TokenSubscriber($tokens);

        $sut->onKernelController($event);
    }

    public function testShouldDoNothingIfControllerIsNotInstanceOfTokenAuthenticatedController(): void
    {
        $validToken = 'dummy-token';
        $tokens = ['lendo' => $validToken];
        $request = Mockery::mock(Request::class);
        $event = new ControllerEvent(
            Mockery::mock(KernelInterface::class),
            [Mockery::mock(Controller::class), 'store'],
            $request,
            1
        );

        $sut = new TokenSubscriber($tokens);

        $this->expectNotToPerformAssertions();

        $sut->onKernelController($event);
    }

    public function controllerProvider(): array
    {
        $lendoToken = 'test-lendo-token';
        $monitoringToken = 'test-monitoring-token';
        $tokens = ['lendo' => $lendoToken, 'monitoring' => $monitoringToken];
        return [
            ['tokens' => $tokens, 'requestTokenKey' => 'lendo-token', 'expectedToken' => $lendoToken, Mockery::mock(WalletController::class)],
            ['tokens' => $tokens, 'requestTokenKey' => 'monitoring-token', 'expectedToken' => $monitoringToken, Mockery::mock(HealthCheckController::class)]
        ];
    }
}
