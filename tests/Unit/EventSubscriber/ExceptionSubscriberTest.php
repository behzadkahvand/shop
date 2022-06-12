<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\ExceptionSubscriber;
use App\Service\ExceptionHandler\MetadataLoader;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

final class ExceptionSubscriberTest extends MockeryTestCase
{
    public function testGettingSubscribedEvents(): void
    {
        $expected = [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];

        self::assertEquals($expected, ExceptionSubscriber::getSubscribedEvents());
    }

    public function testItReturnOnInstancesOfHttpExceptionInterface(): void
    {
        $throwable = new class () extends Exception implements HttpExceptionInterface {
            public function getStatusCode(): void
            {
            }

            public function getHeaders(): void
            {
            }
        };

        $event = new ExceptionEvent(
            Mockery::mock(KernelInterface::class),
            Mockery::mock(Request::class),
            KernelInterface::MASTER_REQUEST,
            $throwable
        );

        $metadataLoader = Mockery::mock(MetadataLoader::class);
        $metadataLoader->shouldNotReceive('getMetadata');

        $listener = new ExceptionSubscriber($metadataLoader);

        $listener->onKernelException($event);
    }

    public function testItConvertThrowableToInternalServerErrorForInvisibleThrowables(): void
    {
        $throwable = new Exception();
        $event = new ExceptionEvent(
            Mockery::mock(KernelInterface::class),
            Mockery::mock(Request::class),
            KernelInterface::MASTER_REQUEST,
            $throwable
        );

        $metadataLoader = Mockery::mock(MetadataLoader::class);
        $metadataLoader->shouldReceive('getMetadata')
            ->once()
            ->with($throwable)
            ->andReturn(new ThrowableMetadata(false, 500, 'invisible throwable'));

        $listener = new ExceptionSubscriber($metadataLoader);

        $listener->onKernelException($event);

        $exception = $event->getThrowable();
        self::assertInstanceOf(HttpException::class, $exception);
        self::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
        self::assertEquals(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], $exception->getMessage());
    }

    public function testItConvertThrowableToHttpException(): void
    {
        $throwable = new Exception();
        $event = new ExceptionEvent(
            Mockery::mock(KernelInterface::class),
            Mockery::mock(Request::class),
            KernelInterface::MASTER_REQUEST,
            $throwable
        );

        $metadataLoader = Mockery::mock(MetadataLoader::class);
        $metadataLoader->shouldReceive('getMetadata')
            ->once()
            ->with($throwable)
            ->andReturn(new ThrowableMetadata(true, 404, 'Not Found'));

        $listener = new ExceptionSubscriber($metadataLoader);

        $listener->onKernelException($event);

        $exception = $event->getThrowable();
        self::assertInstanceOf(HttpException::class, $exception);
        self::assertEquals(Response::HTTP_NOT_FOUND, $exception->getStatusCode());
        self::assertEquals(Response::$statusTexts[Response::HTTP_NOT_FOUND], $exception->getMessage());
    }
}
