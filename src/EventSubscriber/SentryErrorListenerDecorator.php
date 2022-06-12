<?php

namespace App\EventSubscriber;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Sentry\SentryBundle\EventListener\ErrorListener;
use Sentry\SentryBundle\EventListener\ErrorListenerExceptionEvent;
use Throwable;

final class SentryErrorListenerDecorator
{
    private ErrorListener $sentryErrorListener;

    public function __construct(ErrorListener $sentryErrorListener)
    {
        $this->sentryErrorListener = $sentryErrorListener;
    }

    public function handleExceptionEvent(ErrorListenerExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($this->shouldReport($throwable)) {
            $this->sentryErrorListener->handleExceptionEvent($event);
        }
    }

    private function shouldReport(Throwable $throwable): bool
    {
        if (!$throwable instanceof ReportableThrowableInterface) {
            return true;
        }

        return $throwable->shouldReport();
    }
}
