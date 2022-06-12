<?php

namespace App\DTO;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\OrderShipment;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class OrderAddressData
 */
final class OrderAddressData
{
    /**
     * @var Order
     *
     * @Assert\NotBlank(groups={"order.change_address"})
     */
    private $order;

    /**
     * @var CustomerAddress
     *
     * @Assert\NotBlank(groups={"order.change_address"}, message="This field is missing.")
     * @Assert\Expression(
     *     "this.getAddress() and this.getOrder().getCustomer().getId() == this.getAddress().getCustomer().getId()",
     *     message="Given address does not belong to order's customer",
     *     groups={"order.change_address"}
     * )
     *
     * @codingStandardsIgnoreStart
     * @Assert\Expression(
     *     "this.hasCity() and this.getAddress() and this.getOrder().getOrderAddress().getCity().getId() == this.getAddress().getCity().getId()",
     *     message="Given order address city does not match current address city.",
     *     groups={"order.change_address"}
     * )
     * @codingStandardsIgnoreEnd
     */
    private $address;

    /**
     * OrderAddressData constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     *
     * @return OrderAddressData
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     *
     * @return OrderAddressData
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function hasCity()
    {
        $orderAddress = $this->order->getOrderAddress();

        return $orderAddress && $orderAddress->getCity();
    }

    /**
     * @Assert\Callback(groups={"order.change_address"})
     *
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validateShipments(ExecutionContextInterface $context, $payload)
    {
        $statuses = [
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::SENT,
            OrderShipmentStatus::DELIVERED,
            OrderShipmentStatus::AFTER_SALES,
            OrderShipmentStatus::RETURNING,
            OrderShipmentStatus::RETURNED,
            OrderShipmentStatus::THIRD_PARTY_LOGISTICS
        ];

        $shipments = $this->order->getShipments()
                                 ->filter(function (OrderShipment $orderShipment) use ($statuses) {
                                     return in_array($orderShipment->getStatus(), $statuses);
                                 });

        if (count($shipments) > 0) {
            $context
                ->buildViolation('Order shipments statuses does not allow changing order delivery address')
                ->atPath('address')
                ->addViolation()
            ;
        }
    }
}
