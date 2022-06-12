<?php

namespace App\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InternalServerErrorMetadataLoader implements MetadataLoaderInterface
{
    public function load(Throwable $throwable): ThrowableMetadata
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message    = Response::$statusTexts[$statusCode];

        return new ThrowableMetadata(true, $statusCode, $message);
    }

    public function supports(Throwable $throwable): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return -1000;
    }
}
