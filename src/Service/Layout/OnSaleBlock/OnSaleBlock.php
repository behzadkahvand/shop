<?php

namespace App\Service\Layout\OnSaleBlock;

use App\Repository\ProductRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class OnSaleBlock implements OnSaleBlockInterface, CacheableBlockInterface
{
    protected array $ids = [];

    public function __construct(
        private ConfigurationServiceInterface $configuration,
        protected ProductRepository $productRepository,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function generate(array $context = []): array
    {
        $configurationValue = $this->getConfig();

        $this->resolvePriority($configurationValue);
        if (0 === count($this->ids)) {
            return [];
        }

        return $this->findProducts();
    }

    public function getCacheExpiry(): int
    {
        return 360;
    }

    public function getCacheSignature(): string
    {
        $configuration = $this->getConfig();
        $this->resolvePriority($configuration);

        return sprintf('%s_%s', $this->getConfigCode(), implode('_', $this->ids));
    }

    protected function resolvePriority(array $ids): void
    {
        if (count($ids)) {
            $this->ids = collect($ids)
                ->sortBy('priority', SORT_REGULAR, true)
                ->pluck('id')
                ->toArray();
        }
    }

    protected function getConfig(): array
    {
        $configuration = $this->configuration->findByCode($this->getConfigCode());

        return $configuration ? (array)$configuration->getValue() : [];
    }
}
