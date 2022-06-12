<?php

namespace App\Service\Order\AutoConfirm;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Entity\Order;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Order\OrderIsNotConfirmableException;

/**
 * Class ConfigurableAutoConfirmOrderService
 */
final class ConfigurableAutoConfirmOrderService implements AutoConfirmOrderServiceInterface
{
    private AutoConfirmOrderServiceInterface $decorated;
    private ConfigurationServiceInterface $configService;
    private ?Configuration $autoConfirmConfig = null;

    /**
     * ConfigurableAutoConfirmOrderService constructor.
     *
     * @param AutoConfirmOrderServiceInterface $decorated
     * @param ConfigurationServiceInterface $configService
     */
    public function __construct(
        AutoConfirmOrderServiceInterface $decorated,
        ConfigurationServiceInterface $configService
    ) {
        $this->decorated = $decorated;
        $this->configService = $configService;
    }

    /**
     * @inheritDoc
     */
    public function isConfirmable(Order $order): bool
    {
        return $this->orderShouldConfirmAutomatically() && $this->decorated->isConfirmable($order);
    }

    /**
     * @inheritDoc
     */
    public function confirm(Order $order): void
    {
        if (!$this->isConfirmable($order)) {
            throw new OrderIsNotConfirmableException($order);
        }

        $this->decorated->confirm($order);
    }

    /**
     * @return bool
     */
    private function orderShouldConfirmAutomatically(): bool
    {
        $config = $this->getAutoConfirmConfig();

        if (null === $config) {
            return true;
        }

        return true === filter_var($config->getValue(), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return Configuration|null
     */
    private function getAutoConfirmConfig(): ?Configuration
    {
        if (null === $this->autoConfirmConfig) {
            $code = ConfigurationCodeDictionary::AUTO_CONFIRM_ORDER;
            $this->autoConfirmConfig = $this->configService->findByCode($code);
        }

        return $this->autoConfirmConfig;
    }
}
