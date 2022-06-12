<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\Media\Media;
use App\Serializer\Normalizer\MediaNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class MediaNormalizerTest
 */
final class MediaNormalizerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|ObjectNormalizer
     */
    private $objectNormalizer;

    /**
     * @var \Mockery\MockInterface|RouterInterface
     */
    private $router;

    private MediaNormalizer $mediaNormalizer;

    private $areaService;

    private string $mediaBackend;

    protected function setUp(): void
    {
        parent::setUp();

        $this->areaService      = \Mockery::mock(WebsiteAreaService::class);
        $this->objectNormalizer = \Mockery::mock(ObjectNormalizer::class);
        $this->router           = \Mockery::mock(RouterInterface::class);
        $this->mediaBackend     = 'http://localhost';
    }

    protected function tearDown(): void
    {
        unset($this->mediaBackend);

        $this->areaService = null;
        $this->objectNormalizer = null;
        $this->router = null;
    }

    public function testItSupportsInstancesMedia()
    {
        $mediaNormalizer = new MediaNormalizer(
            $this->areaService,
            $this->objectNormalizer,
            $this->router,
            $this->mediaBackend
        );

        self::assertTrue($mediaNormalizer->supportsNormalization(\Mockery::mock(Media::class)));
    }

    public function testItNormalizeMediaPathInAdminArea()
    {
        $media             = \Mockery::mock(Media::class);
        $format            = 'json';
        $context           = [];
        $relativeImagePath = '/foo/bar/baz/image.jpg';

        $this->setupRouter()
             ->setupNormalizer($media, $format, $context, $relativeImagePath);

        $this->areaService->shouldReceive('isAdminArea')->once()->withNoArgs()->andReturnTrue();

        $mediaNormalizer = new MediaNormalizer(
            $this->areaService,
            $this->objectNormalizer,
            $this->router,
            $this->mediaBackend
        );

        $normalized = $mediaNormalizer->normalize($media, $format, $context);

        self::assertArrayHasKey('path', $normalized);
        self::assertArrayHasKey('alt', $normalized);
        self::assertArrayHasKey('url', $normalized);
        self::assertEquals("/foo/bar/baz/image.jpg", $normalized['path']);
        self::assertEquals("{$this->mediaBackend}/foo/bar/baz/image.jpg", $normalized['url']);
    }

    public function testItNormalizeMediaPathInNoneAdminArea()
    {
        $media             = \Mockery::mock(Media::class);
        $format            = 'json';
        $context           = [];
        $relativeImagePath = '/foo/bar/baz/image.jpg';

        $this->setupRouter()
             ->setupNormalizer($media, $format, $context, $relativeImagePath);

        $this->areaService->shouldReceive('isAdminArea')->once()->withNoArgs()->andReturnFalse();

        $mediaNormalizer = new MediaNormalizer(
            $this->areaService,
            $this->objectNormalizer,
            $this->router,
            $this->mediaBackend
        );

        $normalized = $mediaNormalizer->normalize($media, $format, $context);

        self::assertArrayHasKey('path', $normalized);
        self::assertArrayHasKey('alt', $normalized);
        self::assertEquals("{$this->mediaBackend}/foo/bar/baz/image.jpg", $normalized['path']);
    }

    /**
     * @return MediaNormalizerTest
     */
    private function setupRouter(): self
    {
        $requestContext = \Mockery::mock(RequestContext::class);

        $this->router->shouldReceive('getContext')
                     ->once()
                     ->withNoArgs()
                     ->andReturn($requestContext);

        $this->router->shouldReceive('setContext')
                     ->once()
                     ->with(\Mockery::type(RequestContext::class))
                     ->andReturn();

        $this->router->shouldReceive('generate')
                     ->once()
                     ->with('customer.media', [], RouterInterface::ABSOLUTE_URL)
                     ->andReturn($this->mediaBackend);

        $this->router->shouldReceive('setContext')
                     ->once()
                     ->with($requestContext)
                     ->andReturn();

        return $this;
    }

    /**
     * @param Media  $media
     * @param string $format
     * @param array  $context
     * @param string $relativeImagePath
     *
     * @return MediaNormalizerTest
     */
    private function setupNormalizer(Media $media, string $format, array $context, string $relativeImagePath): self
    {
        $this->objectNormalizer->shouldReceive('normalize')
                               ->once()
                               ->with($media, $format, $context)
                               ->andReturn([
                                   'path' => $relativeImagePath,
                                   'alt'  => 'foobar',
                               ]);

        return $this;
    }
}
