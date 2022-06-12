<?php

namespace App\Service\Configuration;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;

class ConfigurationService implements ConfigurationServiceInterface
{
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @inheritDoc
     */
    public function findByCode(string $code): ?Configuration
    {
        return $this->configurationRepository->findOneBy(compact('code'));
    }

    /**
     * @inheritDoc
     */
    public function findByCodes(string ...$codes): array
    {
        $configs = collect($this->configurationRepository->findBy(['code' => $codes]))->keyBy(
            static fn(Configuration $c) => $c->getCode()
        )->toArray();

        foreach ($codes as $code) {
            $configs[$code] ??= null;
        }

        return $configs;
    }
}
