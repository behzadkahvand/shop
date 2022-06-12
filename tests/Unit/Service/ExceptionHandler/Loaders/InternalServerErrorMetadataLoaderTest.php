<?php

namespace App\Tests\Unit\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\Loaders\InternalServerErrorMetadataLoader;
use App\Service\ExceptionHandler\ThrowableMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InternalServerErrorMetadataLoaderTest
 */
final class InternalServerErrorMetadataLoaderTest extends TestCase
{
    public function testItSupportAnyExceptionType()
    {
        $loader = new InternalServerErrorMetadataLoader();

        self::assertTrue($loader->supports(new \Exception()));
    }

    public function testItLoadsMetadata()
    {
        $loader = new InternalServerErrorMetadataLoader();
        $metadata = $loader->load(new \Exception());

        self::assertInstanceOf(ThrowableMetadata::class, $metadata);
        self::assertTrue($metadata->isVisibleForUsers());
        self::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $metadata->getStatusCode());
        self::assertEquals(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], $metadata->getTitle());
    }
}
