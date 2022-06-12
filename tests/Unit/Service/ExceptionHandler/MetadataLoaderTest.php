<?php

namespace App\Tests\Unit\Service\ExceptionHandler;

use App\Service\ExceptionHandler\Loaders\MetadataLoaderInterface;
use App\Service\ExceptionHandler\MetadataLoader;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class MetadataLoaderTest
 */
final class MetadataLoaderTest extends MockeryTestCase
{
    public function testItDelegateLoadingMetadataToLoaders()
    {
        $throwable = new \Exception();
        $metadata  = new ThrowableMetadata(false, 0, '', '');

        $loader = \Mockery::mock(MetadataLoaderInterface::class);
        $loader->shouldReceive('supports')->once()->with($throwable)->andReturnTrue();
        $loader->shouldReceive('load')->once()->with($throwable)->andReturn($metadata);

        $metadataLoader = new MetadataLoader([$loader]);

        self::assertSame($metadata, $metadataLoader->getMetadata($throwable));
    }

    public function testItDelegateLoadingMetadataToFallbackLoader()
    {
        $throwable = new \Exception();
        $metadata  = new ThrowableMetadata(false, 0, '', '');

        $fallbackLoader = \Mockery::mock(MetadataLoaderInterface::class);
        $fallbackLoader->shouldReceive('load')->once()->with($throwable)->andReturn($metadata);

        $metadataLoader = new MetadataLoader([], $fallbackLoader);

        self::assertSame($metadata, $metadataLoader->getMetadata($throwable));
    }
}
