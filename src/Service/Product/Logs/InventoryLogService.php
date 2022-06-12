<?php

namespace App\Service\Product\Logs;

use App\Dictionary\DefaultProductOptionCode;
use App\Dictionary\InventoryStatus;
use App\DTO\InventoryLogData;
use App\DTO\InventoryPriceHistoryData;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOptionValue;
use App\Messaging\Messages\Command\Product\LogInventory;
use App\Messaging\Messages\Command\Product\LogInventoryPriceChange;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\Common\Collections\Collection;

class InventoryLogService
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function checkInventoryIsLoggable(Inventory $inventory, PreUpdateEventArgs $args): InventoryLogData
    {
        $changeStatus       = false;
        $loggableProperties = [
            'inventoryId' => $inventory->getId(),
            'sellerId'    => $inventory->getSeller()->getId(),
            'variantId'   => $inventory->getVariant()->getId(),
        ];

        $allChangeableProperties = $inventory->getAllInventoryChangeableProperties();
        foreach ($allChangeableProperties as $changeableProperty) {
            if (
                $args->hasChangedField($changeableProperty) &&
                ($args->getNewValue($changeableProperty) != $args->getOldValue($changeableProperty))
            ) {
                $changeStatus                                     = true;
                $loggableProperties[$changeableProperty . "From"] = $args->getOldValue($changeableProperty);
                $loggableProperties[$changeableProperty . "To"]   = $args->getNewValue($changeableProperty);
            }
        }

        return new InventoryLogData($changeStatus, $loggableProperties);
    }

    public function dispatchInventoryLogMessage(InventoryLogData $inventoryLogData): void
    {
        $this->messageBus->dispatch(new LogInventory($inventoryLogData->getLoggableProperties()));
    }

    public function hasInventoryPriceChanged(PreUpdateEventArgs $args): bool
    {
        return ($args->hasChangedField('price') && ($args->getNewValue('price') != $args->getOldValue('price')))
            || ($args->hasChangedField('finalPrice') && ($args->getNewValue('finalPrice') != $args->getOldValue('finalPrice')));
    }

    public function dispatchInventoryPriceChangeMessage(
        int $inventoryId,
        ?int $oldPrice,
        ?int $oldFinalPrice,
        ?int $userId = null
    ): void {
        $message = new LogInventoryPriceChange($inventoryId, $oldPrice, $oldFinalPrice, $userId);
        $this->messageBus->dispatch($message);
    }

    public function makeInventoryPriceHistoryDTO(
        Inventory $inventory,
        LogInventoryPriceChange $logInventoryPriceChange
    ): InventoryPriceHistoryData {
        $productVariant = $inventory->getVariant();

        $isActive = $this->checkIsActiveInventory($inventory);

        [$color, $guarantee, $size] = $this->findProperOptions($productVariant->getOptionValues());

        $productInventory = $productVariant->getProduct();

        $seller = $inventory->getSeller();

        return (new InventoryPriceHistoryData())
            ->setInventoryId($inventory->getId())
            ->setSellerId($seller->getId())
            ->setSellerName($seller->getName())
            ->setProductVariantId($productVariant->getId())
            ->setProductId($productInventory->getId())
            ->setProductTitle($productInventory->getTitle())
            ->setPriceFrom($logInventoryPriceChange->getOldPrice() ?? $inventory->getPrice())
            ->setPriceTo($inventory->getPrice())
            ->setFinalPriceFrom($logInventoryPriceChange->getOldFinalPrice() ?? $inventory->getFinalPrice())
            ->setFinalPriceTo($inventory->getFinalPrice())
            ->setIsActive($isActive)
            ->setUserId($logInventoryPriceChange->getUserId())
            ->setIsInventoryBuyBox($this->checkIsInventoryBuyBox($inventory->getId(), $productInventory))
            ->setColor($color)
            ->setGuarantee($guarantee)
            ->setSize($size);
    }

    private function checkIsActiveInventory(Inventory $inventory): bool
    {
        return $inventory->getIsActive() && ($inventory->getSellerStock() > 0) && ($inventory->getStatus() == InventoryStatus::CONFIRMED);
    }

    private function findProperOptions(Collection $optionValues): array
    {
        $color = $guarantee = $size = null;
        /** @var ProductOptionValue $optionValue */
        foreach ($optionValues as $optionValue) {
            switch ($optionValue->getOption()->getCode()) {
                case DefaultProductOptionCode::COLOR:
                    $color = $optionValue;
                    break;
                case DefaultProductOptionCode::GUARANTEE:
                    $guarantee = $optionValue;
                    break;
                default:
                    $size = $optionValue;
            }
        }

        return [$color, $guarantee, $size];
    }

    private function checkIsInventoryBuyBox(int $inventoryId, Product $product): bool
    {
        $buyBox = $product->getBuyBox();
        if (!$buyBox) {
            return false;
        }

        return $inventoryId == $buyBox->getId();
    }
}
