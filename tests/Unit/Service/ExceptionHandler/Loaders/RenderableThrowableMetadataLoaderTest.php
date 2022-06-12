<?php

namespace App\Tests\Unit\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\Loaders\RenderableThrowableMetadataLoader;
use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RenderableThrowableMetadataLoaderTest
 */
final class RenderableThrowableMetadataLoaderTest extends MockeryTestCase
{
    private $translator;

    protected function setUp(): void
    {
        $this->translator = \Mockery::mock(TranslatorInterface::class);
    }

    protected function tearDown(): void
    {
        $this->translator = null;
    }

    public function testItSupportInstancesOfRenderableThrowable()
    {
        $loader = new RenderableThrowableMetadataLoader($this->translator);

        self::assertFalse($loader->supports(new \Exception()));
        self::assertTrue($loader->supports(new class extends \Exception implements RenderableThrowableInterface {
            public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
            {
                return new ThrowableMetadata(false, 500, '');
            }
        }));
    }

    public function testItLoadsMetadataFromThrowable()
    {
        $throwable = new class extends \Exception implements RenderableThrowableInterface {
            public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
            {
                return new ThrowableMetadata(false, 500, '');
            }
        };

        $loader = new RenderableThrowableMetadataLoader($this->translator);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load($throwable));
    }
}
