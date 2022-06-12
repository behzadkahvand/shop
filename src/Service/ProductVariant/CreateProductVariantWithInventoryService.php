<?php

namespace App\Service\ProductVariant;

use App\Dictionary\InventoryStatus;
use App\DTO\Admin\ProductVariantAndInventoryData;
use App\Entity\ProductVariant;
use App\Repository\InventoryRepository;
use App\Repository\ProductVariantRepository;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Service\Inventory\Validation\InventoryPriceValidator;
use App\Service\ProductVariant\Exceptions\InvalidOptionValuesException;
use App\Service\ProductVariant\Exceptions\InvalidLeadTimeException;
use App\Service\ProductVariant\Exceptions\InventoryExistenceException;
use App\Service\ProductVariant\Exceptions\ProductIdentifierException;
use App\Service\ProductVariant\Exceptions\ProductOptionsNotSetException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CreateProductVariantWithInventoryService
{
    protected bool $checkInitialStatus;

    public function __construct(
        protected ProductVariantRepository $productVariantRepository,
        protected InventoryRepository $inventoryRepository,
        protected ProductVariantFactory $factory,
        protected EntityManagerInterface $manager,
        protected MaxInventoryDiscountValidator $discountValidator,
        protected InventoryPriceValidator $inventoryPriceValidator
    ) {
    }

    public function perform(ProductVariantAndInventoryData $productVariantData): ProductVariant
    {
        $this->manager->beginTransaction();

        try {
            $product = $productVariantData->getProduct();

            if (!$product->productIdentifierConstraintIsResolved()) {
                throw ProductIdentifierException::creatingInventoryNotAllowed($product->getId());
            }

            $optionValues = $productVariantData->getOptionValues();

            $optionIds = array_unique(
                $optionValues->map(fn($optionValue) => $optionValue->getOption()->getId())->toArray()
            );

            if ($optionValues->count() !== count($optionIds)) {
                throw new InvalidOptionValuesException();
            }

            $allowedOptionIds = $product->getOptions()->map(fn($option) => $option->getId())->toArray();

            sort($optionIds);
            sort($allowedOptionIds);

            if ($allowedOptionIds !== $optionIds) {
                throw new ProductOptionsNotSetException();
            }

            $optionValueIds = $optionValues->map(fn($optionValue) => $optionValue->getId())->toArray();

            $productVariant = $this->productVariantRepository->findByProductAndOptions($product, $optionValueIds);

            $productVariantIsNew = false;

            if (!$productVariant) {
                $productVariant = $this->factory->getProductVariant();
                $productVariant->setProduct($product);

                $sellerCode = $productVariantData->getCode();
                if ($sellerCode) {
                    $productVariant->setCode($sellerCode);
                }

                foreach ($optionValues as $optionValue) {
                    $productVariant->addOptionValue($optionValue);
                }

                $this->manager->persist($productVariant);
                $this->manager->flush();

                $productVariantIsNew = true;
            }

            $inventory = null;

            $seller = $productVariantData->getSeller();

            if (!$productVariantIsNew) {
                $inventory = $this->inventoryRepository->findOneBy(['variant' => $productVariant, 'seller' => $seller]);
            }

            if ($inventory) {
                throw new InventoryExistenceException();
            }

            $categoryMaxLead = $this->productVariantRepository
                ->getCategoryLeadValueByVariantId($productVariant->getId());

            $leadTime = $productVariantData->getSuppliesIn();

            if (($leadTime > $categoryMaxLead) || ($leadTime < 0)) {
                throw new InvalidLeadTimeException();
            }

            $inventory = $this->factory->getInventory();

            $price      = $productVariantData->getPrice();
            $finalPrice = $productVariantData->getFinalPrice();

            $discount = calc_discount($price, $finalPrice);
            $this->discountValidator->validate($discount);

            $inventory->setSeller($seller)
                      ->setPrice($price)
                      ->setFinalPrice($finalPrice)
                      ->setIsActive($productVariantData->isActive())
                      ->setMaxPurchasePerOrder($productVariantData->getMaxPurchasePerOrder())
                      ->setSellerStock($productVariantData->getStock())
                      ->setLeadTime($leadTime);

            $sellerCode = $productVariantData->getSellerCode();

            if ($sellerCode) {
                $inventory->setSellerCode($sellerCode);
            }

            if ($this->checkInitialStatus && $leadTime === 0) {
                $inventory->setStatus(InventoryStatus::WAIT_FOR_CONFIRM);
            } else {
                $inventory->setStatus(InventoryStatus::CONFIRMED);
            }

            $productVariant->addInventory($inventory);

            $this->inventoryPriceValidator->validate($inventory);

            $this->manager->persist($inventory);
            $this->manager->flush();

            $this->manager->commit();
        } catch (Exception $exception) {
            $this->manager->close();
            $this->manager->rollback();

            throw $exception;
        }

        return $productVariant;
    }

    public function setCheckInitialStatus(bool $checkInitialStatus): void
    {
        $this->checkInitialStatus = $checkInitialStatus;
    }
}
