<?php

namespace App\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\ThrowableMetadata;
use Throwable;

interface MetadataLoaderInterface
{
    public function load(Throwable $throwable): ThrowableMetadata;

    public function supports(Throwable $throwable): bool;

    public static function getPriority(): int;
}
