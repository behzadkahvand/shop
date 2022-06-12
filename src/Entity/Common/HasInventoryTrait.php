<?php

namespace App\Entity\Common;

use App\Dictionary\DefaultProductOptionCode;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use Symfony\Component\Serializer\Annotation\Groups;

trait HasInventoryTrait
{
    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * @Groups({"hasInventory.details"})
     */
    public function getInventoryId(): ?int
    {
        return $this->getInventory()?->getId();
    }

    public function getProduct(): ?Product
    {
        return $this->getVariant()?->getProduct();
    }

    /**
     * @return string|null
     *
     * @Groups({"hasInventory.details"})
     */
    public function getProductTitle(): ?string
    {
        return $this->getProduct()?->getTitle();
    }

    /**
     * @Groups({"hasInventory.details"})
     */
    public function getProductId(): ?int
    {
        return $this->getProduct()?->getId();
    }

    /**
     * @return string|null
     *
     * @Groups({"hasInventory.details"})
     */
    public function getVariantCode()
    {
        return $this->getVariant()?->getCode();
    }

    public function getSeller(): ?Seller
    {
        return $this->getInventory()?->getSeller();
    }

    /**
     * @return string|null
     *
     * @Groups({"hasInventory.details"})
     */
    public function getSellerName(): ?string
    {
        return $this->getSeller()?->getName();
    }

    /**
     * @return string|null
     *
     * @Groups({"hasInventory.details"})
     */
    public function getSellerIdentifier(): ?string
    {
        return $this->getSeller()?->getIdentifier();
    }

    /**
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getColor(): ?ProductOptionValue
    {
        return $this->getVariant()->getOptionValues()->filter(
            function (ProductOptionValue $value) {
                return $value->getOption()->getCode() === DefaultProductOptionCode::COLOR;
            }
        )->first() ?: null;
    }

    /**
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getGuaranty(): ?ProductOptionValue
    {
        return $this->getVariant()->getOptionValues()->filter(
            function (ProductOptionValue $value) {
                return DefaultProductOptionCode::GUARANTEE === $value->getOption()->getCode();
            }
        )->first() ?: null;
    }

    /**
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getOtherOption(): ?ProductOptionValue
    {
        $guaranteeAndColor = [
            DefaultProductOptionCode::GUARANTEE,
            DefaultProductOptionCode::COLOR,
        ];

        return $this->getVariant()->getOptionValues()->filter(
            function (ProductOptionValue $value) use ($guaranteeAndColor) {
                return false === in_array($value->getOption()->getCode(), $guaranteeAndColor);
            }
        )->first() ?: null;
    }

    public function getVariant(): ?ProductVariant
    {
        return $this->getInventory()?->getVariant();
    }
}
