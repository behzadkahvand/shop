<?php

namespace App\Serializer\Normalizer;

use App\Entity\Media\Media;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class MediaNormalizer
 */
final class MediaNormalizer extends AbstractCacheableNormalizer
{
    private string $baseUri;

    private string $mediaBackend;

    private WebsiteAreaService $areaService;

    private ObjectNormalizer $normalizer;

    private RouterInterface $router;

    private bool $isAdminArea;

    /**
     * MediaNormalizer constructor.
     */
    public function __construct(
        WebsiteAreaService $areaService,
        ObjectNormalizer $normalizer,
        RouterInterface $router,
        string $mediaBackend
    ) {
        $this->areaService  = $areaService;
        $this->normalizer   = $normalizer;
        $this->router       = $router;
        $this->mediaBackend = $mediaBackend;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($this->isAdminArea)) {
            $this->isAdminArea = $this->areaService->isAdminArea();
        }

        $media = $this->normalizer->normalize($object, $format, $context);

        if (isset($media['path'])) {
            $media[$this->isAdminArea ? 'url' : 'path'] = $this->makePathAbsolute($media['path']);
        }

        return $media;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Media;
    }

    /**
     * @param string $mediaPath
     *
     * @return string
     */
    private function makePathAbsolute(string $mediaPath): string
    {
        return rtrim($this->getBaseUri(), '/') . '/' . ltrim($mediaPath, '/');
    }

    /**
     * @return string
     */
    private function getBaseUri(): string
    {
        if (!isset($this->baseUri)) {
            $requestContext = $this->router->getContext();

            $this->router->setContext(RequestContext::fromUri($this->mediaBackend));

            $this->baseUri = $this->router->generate('customer.media', [], RouterInterface::ABSOLUTE_URL);

            $this->router->setContext($requestContext);
        }

        return $this->baseUri;
    }
}
