<?php

namespace App\Service\Discount;

use App\Dictionary\InventoryDiscount;
use App\Exceptions\Discount\InventoryDiscountRuleViolationException;
use App\Service\Configuration\ConfigurationServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaxInventoryDiscountValidator
{
    protected int $limit;

    public function __construct(
        protected ConfigurationServiceInterface $config,
        protected TranslatorInterface $translator,
        protected int $defaultLimit
    ) {
        $this->setLimit();
    }

    /**
     * @throws InventoryDiscountRuleViolationException
     */
    public function validate(int $discount): void
    {
        if ($discount > $this->limit) {
            throw new InventoryDiscountRuleViolationException(
                $this->translator->trans('max_inventory_discount_exceeded', ['limit' => $this->limit], 'exceptions')
            );
        }
    }

    private function setLimit(): void
    {
        $configuration = $this->config->findByCode(InventoryDiscount::MAX_LIMIT);
        if (isset($configuration)) {
            $this->limit = $configuration->getValue();
        } else {
            $this->limit = $this->defaultLimit;
        }
    }
}
