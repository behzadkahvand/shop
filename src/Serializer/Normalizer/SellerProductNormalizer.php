<?php

namespace App\Serializer\Normalizer;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SellerProductNormalizer extends AbstractCacheableNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;

    private WebsiteAreaService $areaService;

    private Security $security;

    private bool $isSellerArea;

    public function __construct(
        ObjectNormalizer $normalizer,
        WebsiteAreaService $websiteAreaService,
        Security $security
    ) {
        $this->normalizer  = $normalizer;
        $this->security    = $security;
        $this->areaService = $websiteAreaService;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $user           = $this->security->getUser();
        $inventoryCount = $object->getInventories()->filter(function (Inventory $inventory) use ($user) {
            return $inventory->getSeller()->getId() === $user->getId();
        })->count();

        return array_merge($normalizedData, [
            'inventoryCount' => $inventoryCount,
            'isSeller'       => 0 < $inventoryCount,
        ]);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        if (!isset($this->isSellerArea)) {
            $this->isSellerArea = $this->areaService->isSellerArea();
        }

        return $this->isSellerArea && $data instanceof Product && $this->hasValidGroups($context);
    }

    protected function hasValidGroups(array $context): bool
    {
        $groups = $context['groups'] ?? [];

        if (!is_array($context['groups'])) {
            $groups = [$context['groups']];
        }

        $validGroups = ['seller.products.index', 'seller.product.search'];

        return !empty(array_intersect($validGroups, $groups));
    }
}
