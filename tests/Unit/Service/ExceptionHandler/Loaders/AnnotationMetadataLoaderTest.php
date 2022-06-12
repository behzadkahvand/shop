<?php

namespace App\Tests\Unit\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\Annotations\Metadata;
use App\Service\ExceptionHandler\Loaders\AnnotationMetadataLoader;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Doctrine\Common\Annotations\Reader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AnnotationMetadataLoaderTest
 */
final class AnnotationMetadataLoaderTest extends MockeryTestCase
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

    public function testItDoesNotSupportThrowablesWithoutMappingAnnotation()
    {
        $reader = \Mockery::mock(Reader::class);
        $reader->shouldReceive('getClassAnnotation')
               ->once()
               ->with(\Mockery::type(\ReflectionClass::class), Metadata::class)
               ->andReturnNull();

        $loader = new AnnotationMetadataLoader($reader, $this->translator);

        self::assertFalse($loader->supports(new \Exception()));
    }

    public function testItSupportThrowablesWithMappingAnnotation()
    {
        $reader = \Mockery::mock(Reader::class);
        $reader->shouldReceive('getClassAnnotation')
               ->once()
               ->with(\Mockery::type(\ReflectionClass::class), Metadata::class)
               ->andReturn(\Mockery::mock(Metadata::class));

        $loader = new AnnotationMetadataLoader($reader, $this->translator);

        self::assertTrue($loader->supports(new \Exception()));
    }

    public function testItLoadsThrowableMetadataFromClassAnnotation()
    {
        $metadata = new Metadata([]);

        $reader = \Mockery::mock(Reader::class);
        $reader->shouldReceive('getClassAnnotation')
               ->once()
               ->with(\Mockery::type(\ReflectionClass::class), Metadata::class)
               ->andReturn($metadata);

        $loader = new AnnotationMetadataLoader($reader, $this->translator);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load(new \Exception()));
    }

    public function testItTranslateMessageWithoutData()
    {
        $throwable      = new \Exception();
        $translationKey = 'translation_key';
        $metadata       = new Metadata([
            'detail' => [
                'translation' => ['key' => $translationKey],
            ],
        ]);

        $reader = \Mockery::mock(Reader::class);
        $reader->shouldReceive('getClassAnnotation')
               ->once()
               ->with(\Mockery::type(\ReflectionClass::class), Metadata::class)
               ->andReturn($metadata);

        $this->translator->shouldReceive('trans')
                         ->once()
                         ->with($translationKey, [], 'exceptions', 'fa')
                         ->andReturn('پیام ترجمه شده');

        $loader = new AnnotationMetadataLoader($reader, $this->translator);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load($throwable));
    }

    public function testItTranslateMessageWithData()
    {
        $parameters = [
            'foo' => 'bar'
        ];

        $throwable      = new class ($parameters) extends \Exception {
            private array $parameters;
            public function __construct(array $parameters)
            {
                parent::__construct('', 0, null);
                $this->parameters = $parameters;
            }

            public function getData()
            {
                return $this->parameters;
            }
        };

        $translationKey = 'translation_key';
        $metadata       = new Metadata([
            'detail' => [
                'translation' => [
                    'key' => $translationKey,
                    'dataMethod' => 'getData',
                ],
            ],
        ]);

        $reader = \Mockery::mock(Reader::class);
        $reader->shouldReceive('getClassAnnotation')
               ->once()
               ->with(\Mockery::type(\ReflectionClass::class), Metadata::class)
               ->andReturn($metadata);

        $this->translator->shouldReceive('trans')
                         ->once()
                         ->with($translationKey, $parameters, 'exceptions', 'fa')
                         ->andReturn('پیام ترجمه شده');

        $loader = new AnnotationMetadataLoader($reader, $this->translator);

        self::assertInstanceOf(ThrowableMetadata::class, $loader->load($throwable));
    }
}
