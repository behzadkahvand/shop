<?php

namespace App\Service\Product\Seller\InventoryUpdate;

use App\Entity\Inventory;
use App\Exceptions\Discount\InventoryDiscountRuleViolationException;
use App\Exceptions\Inventory\InventoryPriceRuleViolationException;
use App\Repository\InventoryRepository;
use App\Repository\ProductVariantRepository;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Service\Inventory\Validation\InventoryPriceValidator;
use App\Service\Product\Seller\InventoryValidationConstraintsFactory;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryUpdater
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected InventoryValidationConstraintsFactory $constraintsFactory,
        protected ProductVariantRepository $productVariantRepository,
        protected PropertyAccessorInterface $propertyAccessor,
        protected MaxInventoryDiscountValidator $maxInventoryDiscountValidator,
        protected InventoryRepository $inventoryRepository,
        protected TranslatorInterface $translator,
        protected InventoryPriceValidator $inventoryPriceValidator
    ) {
    }

    /**
     * @throws InventoryDiscountRuleViolationException
     * @throws InventoryPriceRuleViolationException
     */
    public function update(Inventory $inventory, array $data)
    {
        $categoryMaxLead = $this->productVariantRepository
            ->getCategoryLeadValueByVariantId($inventory->getVariant()->getId());

        $campaignInventoriesCount = $this->inventoryRepository->countProductCampaignInventories($inventory->getProduct());
        $isCampaignProduct = $campaignInventoriesCount > 0;

        $constraints = [
            'isActive' => $this->constraintsFactory->getIsActiveConstraints(true),
            'leadTime' => $this->constraintsFactory->getLeadTimeConstraints($categoryMaxLead, true),
            'price' => $this->constraintsFactory->getPriceConstraints($isCampaignProduct, true),
            'finalPrice' => $this->constraintsFactory->getFinalPriceConstraints($isCampaignProduct, true),
            'sellerStock' => $this->constraintsFactory->getSellerStockConstraints($isCampaignProduct, true),
            'maxPurchasePerOrder' => $this->constraintsFactory->getMaxPurchasePerOrderConstraints(true),
            'sellerCode' => $this->constraintsFactory->getSellerCodeConstraints(true)
        ];

        foreach ($data as $key => $value) {
            $currentValue = $this->propertyAccessor->getValue($inventory, $key);
            if ($currentValue === $value) {
                continue;
            }

            $violations = $this->validator->validate($value, $constraints[$key]);

            if ($violations->count() > 0) {
                throw new ValidationFailedException($value, $violations);
            }
        }

        $this->validateDiscount($data);

        $this->validatePrice($inventory, $data);

        foreach ($data as $key => $value) {
            $this->propertyAccessor->setValue($inventory, $key, $value);
        }
    }

    /**
     * @throws InventoryDiscountRuleViolationException
     */
    public function validateDiscount(array $data): void
    {
        $discount = calc_discount($data['price'], $data['finalPrice']);

        if ($discount < 0) {
            throw new InventoryDiscountRuleViolationException(
                $this->translator->trans('negative_inventory_discount', domain: 'exceptions')
            );
        }

        $this->maxInventoryDiscountValidator->validate($discount);
    }

    /**
     * @throws InventoryPriceRuleViolationException
     */
    private function validatePrice(Inventory $inventory, array $data): void
    {
        $cloned = clone $inventory;
        $cloned->setPrice($data['price']);

        $this->inventoryPriceValidator->validate($cloned);
    }
}
