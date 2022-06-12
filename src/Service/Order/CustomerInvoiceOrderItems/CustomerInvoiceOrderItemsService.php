<?php

namespace App\Service\Order\CustomerInvoiceOrderItems;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Configuration\ConfigurationServiceInterface;

class CustomerInvoiceOrderItemsService
{
    protected ConfigurationServiceInterface $configurationService;

    public function __construct(ConfigurationServiceInterface $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function get(Order $order): array
    {
        $orderItems         = $order->getOrderItems();
        $excludedSellers    = $this->getExcludedSellers();
        $excludedCategories = $this->getExcludedCategories();

        if (empty($excludedSellers) && empty($excludedCategories)) {
            return $orderItems->toArray();
        }

        $callback = function (OrderItem $orderItem) use ($excludedSellers, $excludedCategories) {
            $inventory  = $orderItem->getInventory();
            $sellerId   = $inventory->getSeller()->getId();
            $categoryId = $inventory->getVariant()->getProduct()->getCategory()->getId();

            return !in_array($sellerId, $excludedSellers, true) && !in_array($categoryId, $excludedCategories, true);
        };

        return $orderItems->filter($callback)->getValues();
    }

    /**
     * @return array<integer>
     */
    protected function getExcludedSellers(): array
    {
        $config = $this->configurationService->findByCode(
            ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_SELLERS
        );

        if ($config === null || $config->getValue() === null) {
            return [];
        }

        return array_map(fn($v) => (int)$v, (array)$config->getValue());
    }

    /**
     * @return array<integer>
     */
    protected function getExcludedCategories(): array
    {
        $config = $this->configurationService->findByCode(
            ConfigurationCodeDictionary::CUSTOMER_INVOICE_EXCLUDED_CATEGORIES
        );

        if ($config === null || $config->getValue() === null) {
            return [];
        }

        return array_map(fn($v) => (int)$v, (array)$config->getValue());
    }
}
