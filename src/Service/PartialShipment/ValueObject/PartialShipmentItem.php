<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\Inventory;
use App\Entity\ShippingCategory;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class PartialShipmentItem
 */
class PartialShipmentItem implements \JsonSerializable
{
    /**
     * @var Inventory
     * @Groups({"cart.shipments"})
     */
    private Inventory $inventory;

    /**
     * @var int
     */
    private int $quantity;

    /**
     * @var float|int
     */
    private int $price;

    /**
     * @var ShippingCategory|null
     */
    private ShippingCategory $shippingCategory;

    /**
     * @var int|null
     */
    private int $suppliesIn;

    /**
     * @var int
     */
    private int $subTotal;

    /**
     * @var int
     */
    private int $grandTotal;

    /**
     * @var array<string>
     */
    private array $messages;

    public function __construct(
        Inventory $inventory,
        int $quantity,
        int $subTotal,
        int $grandTotal,
        array $messages = []
    ) {
        $this->inventory        = $inventory;
        $this->quantity         = $quantity;
        $this->price            = $inventory->getFinalPrice() * $this->quantity;
        $this->shippingCategory = $inventory->getVariant()->getProduct()->getShippingCategory();
        $this->suppliesIn       = $inventory->getSafeSuppliesIn();
        $this->subTotal         = $subTotal;
        $this->grandTotal       = $grandTotal;
        $this->messages         = $messages;
    }

    /**
     * @return Inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return float|int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getSubTotal(): int
    {
        return $this->subTotal;
    }

    /**
     * @return int
     */
    public function getGrandTotal(): int
    {
        return $this->grandTotal;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return ShippingCategory|null
     */
    public function getShippingCategory(): ?ShippingCategory
    {
        return $this->shippingCategory;
    }

    /**
     * @return int|null
     */
    public function getSuppliesIn(): ?int
    {
        return $this->suppliesIn;
    }

    /**
     * @return int
     */
    public function getShippingCategoryId(): int
    {
        return $this->getShippingCategory()->getId();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'price'      => $this->inventory->getPrice(),
            'subTotal'   => $this->subTotal,
            'grandTotal' => $this->grandTotal,
            'quantity'   => $this->quantity,
            'inventory'  => $this->inventory,
            'messages'   => $this->messages,
        ];
    }
}
