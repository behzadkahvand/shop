<?php

namespace App\Tests\Unit\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\Factories\AbstractMetadataFactory;
use App\Service\ExceptionHandler\Loaders\MetadataLoaderInterface;
use App\Service\ExceptionHandler\Loaders\StaticListMetadataLoader;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class StaticListMetadataLoaderTest
 */
final class StaticListMetadataLoaderTest extends MockeryTestCase
{
    private $translator;

    private $container;

    private $fallbackLoader;

    protected function setUp(): void
    {
        $this->translator     = \Mockery::mock(TranslatorInterface::class);
        $this->container      = \Mockery::mock(ContainerInterface::class);
        $this->fallbackLoader = \Mockery::mock(MetadataLoaderInterface::class);
    }

    protected function tearDown(): void
    {
        $this->translator = null;
        $this->container = null;
        $this->fallbackLoader = null;
    }

    public function testItSupportsClassHierarchyIfFactoryIsCallable()
    {
        $throwable = new \InvalidArgumentException();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => function () {
            },
        ]);

        self::assertTrue($loader->supports($throwable));

        $loader->setFactories([
            \Exception::class => function () {
            },
        ]);

        self::assertTrue($loader->supports($throwable));

        $loader->setFactories([
            \Throwable::class => function () {
            },
        ]);

        self::assertTrue($loader->supports($throwable));
    }

    public function testItSupportsClassHierarchyIfFactoryIsInstanceOfAbstractMetadataFactory()
    {
        $subClassOfAbstractMetadataFactory = new class ($this->container) extends AbstractMetadataFactory {
            public function __invoke(\Throwable $throwable, TranslatorInterface $translator): ThrowableMetadata
            {
            }
        };

        $this->container->shouldReceive('has')
                        ->once()
                        ->with(get_class($subClassOfAbstractMetadataFactory))
                        ->andReturnTrue();

        $throwable = new \InvalidArgumentException();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => get_class($subClassOfAbstractMetadataFactory),
        ]);

        self::assertTrue($loader->supports($throwable));

        $this->container->shouldReceive('has')
                        ->once()
                        ->with(get_class($subClassOfAbstractMetadataFactory))
                        ->andReturnFalse();

        self::assertFalse($loader->supports($throwable));
    }

    public function testItDoesNotSupportIfFactoryIsNotCallableOrInstanceOfAbstractMetadataFactory()
    {
        $throwable = new \InvalidArgumentException();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => get_class(new \stdClass()),
        ]);

        self::assertFalse($loader->supports($throwable));
    }

    public function testItDoesNotSupportIfStaticListDoesNotHasFactoryForThrowable()
    {
        $throwable = new \InvalidArgumentException();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([]);

        self::assertFalse($loader->supports($throwable));
    }

    public function testItThrowExceptionIfFactoryIsNotCallableOrContainerDoesNotHasGivenFactory()
    {
        $factory   = get_class(new \stdClass());
        $throwable = new \InvalidArgumentException();

        $this->container->shouldReceive('has')->once()->with($factory)->andReturnFalse();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => $factory,
        ]);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Expected a callable as throwable metadata factory got string');

        $loader->load($throwable);
    }

    public function testItLoadThrowableMetadataWithCallable()
    {
        $throwable = new \InvalidArgumentException();

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => function () {
                return new ThrowableMetadata(false, 500, '', '');
            },
        ]);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load($throwable));
    }

    public function testItLoadThrowableMetadataWithAbstractMetadataFactory()
    {
        $subClassOfAbstractMetadataFactory = new class ($this->container) extends AbstractMetadataFactory {
            public function __invoke(\Throwable $throwable, TranslatorInterface $translator): ThrowableMetadata
            {
                return new ThrowableMetadata(false, 500, '', '');
            }
        };

        $throwable = new \InvalidArgumentException();

        $this->container->shouldReceive('has')
                        ->once()
                        ->with(get_class($subClassOfAbstractMetadataFactory))
                        ->andReturnTrue();

        $this->container->shouldReceive('get')
                        ->once()
                        ->with(get_class($subClassOfAbstractMetadataFactory))
                        ->andReturn($subClassOfAbstractMetadataFactory);

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([
            \InvalidArgumentException::class => get_class($subClassOfAbstractMetadataFactory),
        ]);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load($throwable));
    }

    public function testItDelegateLoadingMetadataToFallbackLoader()
    {
        $throwable = new \InvalidArgumentException();

        $fallbackResponse = new ThrowableMetadata(true, 500, '', '');

        $this->fallbackLoader->shouldReceive('load')
                             ->once()
                             ->with($throwable)
                             ->andReturn($fallbackResponse);

        $loader = new StaticListMetadataLoader($this->translator, $this->container, $this->fallbackLoader);
        $loader->setFactories([]);

        self::assertSame($fallbackResponse, $loader->load($throwable));
    }

    public function testItUseInternalServerErrorLoaderAsDefaultFallbackLoader()
    {
        $throwable = new \InvalidArgumentException();
        $fallbackResponse = new ThrowableMetadata(true, 500, 'Internal Server Error', 'Internal Server Error');

        $loader = new StaticListMetadataLoader($this->translator, $this->container);
        $loader->setFactories([]);

        self::assertEquals($fallbackResponse, $loader->load($throwable));
    }
}
