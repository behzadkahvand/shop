<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Service\PartialShipment\Exceptions\MinimumShipmentItemCountException;
use DateTimeInterface;

/**
 * Class BaseFreezedPartialShipment
 */
class BaseFreezedPartialShipment
{
    /**
     * @var array|PartialShipmentItem[]
     */
    private array $shipmentItems;

    /**
     * @var ShippingMethod
     */
    private ShippingMethod $shippingMethod;

    /**
     * @var PartialShipmentPrice
     */
    private PartialShipmentPrice $price;

    /**
     * @var DateTimeInterface
     */
    private DateTimeInterface $deliveryDate;

    /**
     * @var string
     */
    private string $title;

    /**
     * @var ShippingCategory
     */
    private ShippingCategory $shippingCategory;

    /**
     * @var string|null
     */
    private ?string $description;

    /**
     * BaseFreezedPartialShipment constructor.
     *
     * @param array $shipmentItems
     * @param ShippingMethod $shippingMethod
     * @param PartialShipmentPrice $price
     * @param DateTimeInterface $deliveryDate
     * @param string $title
     * @param string|null $description
     */
    public function __construct(
        array $shipmentItems,
        ShippingMethod $shippingMethod,
        PartialShipmentPrice $price,
        DateTimeInterface $deliveryDate,
        string $title,
        ?string $description = null
    ) {
        if (empty($shipmentItems)) {
            throw new MinimumShipmentItemCountException(sprintf('%s needs at least one shipment item', __CLASS__));
        }

        $this->shipmentItems  = $shipmentItems;
        $this->shippingMethod = $shippingMethod;
        $this->price          = $price;
        $this->deliveryDate   = $deliveryDate;
        $this->title          = $title;
        $this->shippingCategory = current($shipmentItems)->getShippingCategory();
        $this->description      = $description;
    }

    /**
     * @return PartialShipmentItem[]|array
     */
    public function getShipmentItems()
    {
        return $this->shipmentItems;
    }

    /**
     * @return ShippingMethod
     */
    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    /**
     * @return PartialShipmentPrice
     */
    public function getPrice(): PartialShipmentPrice
    {
        return $this->price;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDeliveryDate(): DateTimeInterface
    {
        return $this->deliveryDate;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function getShippingCategory(): ShippingCategory
    {
        return $this->shippingCategory;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param Order $order
     *
     * @return OrderShipment
     */
    public function toOrderShipment(Order $order): OrderShipment
    {
        $orderShipment = new OrderShipment();
        $orderShipment->setTitle($this->title)
                      ->setSubTotal($this->price->getSubTotal())
                      ->setGrandTotal($this->price->getGrandTotal())
                      ->setOrder($order)
                      ->setDeliveryDate($this->deliveryDate)
                      ->setMethod($this->shippingMethod)
                      ->setShippingCategory($this->shippingCategory)
                      ->setDescription($this->description)
                      ->setPodCode(random_int(10000, 99999));

        $inventoryIds = collect($this->shipmentItems)->map(function (PartialShipmentItem $item) {
            return $item->getInventory()->getId();
        });

        $order->getOrderItems()
              ->filter(function (OrderItem $orderItem) use ($inventoryIds) {
                  return $inventoryIds->contains($orderItem->getInventory()->getId());
              })
              ->forAll(function ($index, OrderItem $orderItem) use ($orderShipment) {
                  $orderShipment->addOrderItem($orderItem);

                  return true;
              });

        return $orderShipment;
    }
}
