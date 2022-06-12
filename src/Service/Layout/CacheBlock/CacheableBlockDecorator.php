<?php

namespace App\Service\Layout\CacheBlock;

use App\Service\Layout\LayoutInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class CacheableBlockDecorator implements LayoutInterface
{
    public function __construct(
        private CacheableBlockInterface $decorated,
        private TagAwareCacheInterface $cache,
        private NormalizerInterface $normalizer
    ) {
    }

    public function getCode(): string
    {
        return $this->decorated->getCode();
    }

    public function generate(array $context = []): array
    {
        return $this->cache->get(
            CacheableBlockInterface::CACHE_PREFIX . $this->decorated->getCacheSignature($context),
            function (ItemInterface $item) use ($context) {
                $item->tag($this->getCacheTags())->expiresAfter($this->decorated->getCacheExpiry());

                $result = $this->decorated->generate($context);

                if (isset($context['serialization_groups'])) {
                    $result = $this->normalizer->normalize($result, null, [
                        'groups' => $context['serialization_groups']
                    ]);
                }

                return $result;
            }
        );
    }

    abstract protected function getCacheTags(): array;
}
