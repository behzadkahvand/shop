<?php

namespace App\Service\Log;

use App\Document\InventoryLog;
use App\Document\InventoryPriceHistoryLog;
use App\Document\InventoryPriceLog;
use App\Document\ProductOptionValue;
use App\Document\SearchLog;
use App\DTO\InventoryPriceHistoryData;
use DateTime;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class DataLoggerService
{
    public function __construct(private DocumentManager $documentManager)
    {
    }

    public function logInventoryPriceChange(InventoryPriceHistoryData $priceHistoryData): void
    {
        $inventoryLog = (new InventoryPriceLog())
            ->setId($priceHistoryData->getInventoryId())
            ->setProductId($priceHistoryData->getProductId())
            ->setProductTitle($priceHistoryData->getProductTitle())
            ->setSellerId($priceHistoryData->getSellerId())
            ->setSellerName($priceHistoryData->getSellerName())
            ->setProductVariantId($priceHistoryData->getProductVariantId())
            ->setPriceFrom($priceHistoryData->getPriceFrom())
            ->setPriceTo($priceHistoryData->getPriceTo())
            ->setFinalPriceFrom($priceHistoryData->getFinalPriceFrom())
            ->setFinalPriceTo($priceHistoryData->getFinalPriceTo())
            ->setIsActive($priceHistoryData->getIsActive())
            ->setIsBuyBox($priceHistoryData->getIsInventoryBuyBox());

        $inventoryPriceHistoryDocument = (new InventoryPriceHistoryLog())
            ->setInventory($inventoryLog)
            ->setColor($this->productOptionDocumentFactory($priceHistoryData->getColor()))
            ->setGuarantee($this->productOptionDocumentFactory($priceHistoryData->getGuarantee()))
            ->setSize($this->productOptionDocumentFactory($priceHistoryData->getSize()))
            ->setUserId($priceHistoryData->getUserId())
            ->setCreatedAt(new DateTime("now"));

        $this->documentManager->persist($inventoryPriceHistoryDocument);
        $this->documentManager->flush();
    }

    protected function productOptionDocumentFactory(?\App\Entity\ProductOptionValue $productOptionValue): ?ProductOptionValue
    {
        if (!$productOptionValue) {
            return null;
        }

        return (new ProductOptionValue())
            ->setId($productOptionValue->getId())
            ->setCode($productOptionValue->getCode())
            ->setValue($productOptionValue->getValue())
            ->setOptionId($productOptionValue->getOption()->getId())
            ->setAttributes($productOptionValue->getAttributes());
    }

    public function logProductSearch(
        string $term,
        int $resultCount,
        ?int $customerId,
        ?DateTimeInterface $createdAt = null,
        bool $flushToDB = true
    ): void {
        $searchLogDocument = (new SearchLog())
            ->setTerm($term)
            ->setResultCount($resultCount)
            ->setCustomerId($customerId)
            ->setCreatedAt($createdAt ?? new DateTime("now"));

        $this->documentManager->persist($searchLogDocument);

        if ($flushToDB) {
            $this->documentManager->flush();
        }
    }

    public function logInventory(array $loggableProperties): void
    {
        $this->documentManager->persist(new InventoryLog($loggableProperties));
        $this->documentManager->flush();
    }
}
