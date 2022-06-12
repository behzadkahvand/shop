<?php

namespace App\Service\Inventory\Validation;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Exceptions\Inventory\InventoryPriceRuleViolationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryPriceValidator
{
    public function __construct(protected TranslatorInterface $translator)
    {
    }

    /**
     * @throws InventoryPriceRuleViolationException
     */
    public function validate(Inventory $inventory): void
    {
        $this->validateReferencePriceRule($inventory);
    }

    /**
     * @throws InventoryPriceRuleViolationException
     */
    public function validateReferencePriceRule(Inventory $inventory): void
    {
        $inventoryPrice = $inventory->getPrice();
        $product = $inventory->getProduct();

        if (null === $product->getReferencePrice()) {
            return;
        }

        if ($inventoryPrice > $this->maxValidPrice($product) || $inventoryPrice < $this->minValidPrice($product)) {
            throw new InventoryPriceRuleViolationException(
                $this->translator->trans('inventory_reference_price_rule_violated', domain: 'exceptions')
            );
        }
    }

    protected function maxValidPrice(Product $product): int
    {
        $referencePrice = $product->getReferencePrice();
        $topMargin = $product->getPriceTopMargin();

        return $referencePrice + ($topMargin / 100) * $referencePrice;
    }

    protected function minValidPrice(Product $product): int
    {
        $referencePrice = $product->getReferencePrice();
        $bottomMargin = $product->getPriceBottomMargin();

        return $referencePrice - ($bottomMargin / 100) * $referencePrice;
    }
}
