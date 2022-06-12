<?php

namespace App\Service\PartialShipment;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Entity\Zone;
use App\Service\PartialShipment\Factory\PartialShipmentFactory;
use App\Service\PartialShipment\Grouping\PartialShipmentItemGrouperInterface;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ValidatablePartialShipmentInterface;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\Zones\ZoneDetector\ZoneDetector;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Class PartialShipmentService
 */
class PartialShipmentService
{
    /**
     * @var ZoneDetector
     */
    private ZoneDetector $zoneDetector;

    /**
     * @var PartialShipmentItemGrouperInterface
     */
    private PartialShipmentItemGrouperInterface $itemGrouper;

    /**
     * @var PartialShipmentFactory
     */
    private PartialShipmentFactory $partialShipmentFactory;

    /**
     * @var PartialShipmentValidator
     */
    private PartialShipmentValidator $validator;

    /**
     * PartialShipmentService constructor.
     *
     * @param ZoneDetector $zoneDetector
     * @param PartialShipmentItemGrouperInterface $itemGrouper
     * @param PartialShipmentFactory $partialShipmentFactory
     * @param PartialShipmentValidator $validator
     */
    public function __construct(
        ZoneDetector $zoneDetector,
        PartialShipmentItemGrouperInterface $itemGrouper,
        PartialShipmentFactory $partialShipmentFactory,
        PartialShipmentValidator $validator
    ) {
        $this->zoneDetector           = $zoneDetector;
        $this->itemGrouper            = $itemGrouper;
        $this->partialShipmentFactory = $partialShipmentFactory;
        $this->validator              = $validator;
    }

    /**
     * @param Cart            $cart
     * @param CustomerAddress $address
     * @param array           $selectedValues
     * @param bool            $isExpressDelivery
     *
     * @return array|BaseFreezedPartialShipment[]
     * @throws \Exception
     */
    public function getPartialShipments(
        Cart $cart,
        CustomerAddress $address,
        array $selectedValues,
        bool $isExpressDelivery
    ): array {
        $violations     = [];
        $shipments      = [];
        $selectedValues = collect($selectedValues)->keyBy(fn($sv) => $sv->getId())->toArray();

        foreach ($this->createFromCart($cart, $address, $isExpressDelivery) as $i => $partialShipment) {
            if (!$partialShipment instanceof ValidatablePartialShipmentInterface) {
                $shipments[] = $partialShipment->freeze($partialShipment->getBaseDeliveryDate());

                continue;
            }

            $id = $partialShipment->getId();

            if (!isset($selectedValues[$id])) {
                $violations = $this->addMissingPartialShipmentViolation($violations, $id);

                continue;
            }

            $selectedDatetime = $selectedValues[$id]->getDeliveryDate();
            $validationErrors = $this->validator->validate($partialShipment, $selectedDatetime);

            if (0 < count($validationErrors)) {
                array_push($violations, ...iterator_to_array($validationErrors));

                continue;
            }

            if (empty($violations)) {
                $shipments[] = $partialShipment->freeze($selectedDatetime);
            }
        }

        if (0 < count($violations)) {
            throw new ValidationFailedException($selectedValues, new ConstraintViolationList($violations));
        }

        return $shipments;
    }

    /**
     * @param Cart            $cart
     * @param CustomerAddress $address
     * @param bool            $isExpressDelivery
     *
     * @return array|AbstractPartialShipment[]
     */
    public function createFromCart(Cart $cart, CustomerAddress $address, bool $isExpressDelivery = true): array
    {
        $zone          = $this->zoneDetector->getZoneForCustomerAddress($address);
        $shipmentItems = $cart->getCartItems()->map(function (CartItem $cartItem) {
            return new PartialShipmentItem(
                $cartItem->getInventory(),
                $cartItem->getQuantity(),
                $cartItem->getSubtotal(),
                $cartItem->getGrandTotal(),
                $cartItem->getMessages()
            );
        });

        return $this->createFromShipmentItems($shipmentItems->toArray(), $zone, $isExpressDelivery);
    }

    /**
     * @param CustomerAddress $address
     * @param bool $isExpressDelivery
     * @param Inventory $inventory
     *
     * @return AbstractPartialShipment[]
     */
    public function createFromInventory(Inventory $inventory, CustomerAddress $address, bool $isExpressDelivery): array
    {
        $zone          = $this->zoneDetector->getZoneForCustomerAddress($address);
        $shipmentItems = [
            new PartialShipmentItem($inventory, 1, $inventory->getPrice(), $inventory->getFinalPrice(), [])
        ];

        return $this->createFromShipmentItems($shipmentItems, $zone, $isExpressDelivery);
    }

    /**
     * @param array $shipmentItems
     * @param Zone  $zone
     * @param bool  $isExpressDelivery
     *
     * @return array|AbstractPartialShipment[]
     */
    private function createFromShipmentItems(array $shipmentItems, Zone $zone, bool $isExpressDelivery): array
    {
        $groupedShipmentItems = $this->itemGrouper->group($shipmentItems);

        return $this->calculatePartialShipments($groupedShipmentItems, $zone, $isExpressDelivery);
    }

    /**
     * @param iterable $groupedShipmentItems
     * @param Zone $zone
     * @param bool $isExpressDelivery
     *
     * @return array
     */
    private function calculatePartialShipments(
        iterable $groupedShipmentItems,
        Zone $zone,
        bool $isExpressDelivery
    ): array {
        $orderDateTime = new \DateTimeImmutable();
        $partialShipments = [];
        $factory          = $this->partialShipmentFactory;

        foreach ($groupedShipmentItems as $groupId => $shipmentItems) {
            $partialShipments[] = $factory->create($orderDateTime, $zone, $shipmentItems, $isExpressDelivery);
        }

        return $partialShipments;
    }

    /**
     * @param array $violations
     * @param int   $id
     *
     * @return array
     */
    private function addMissingPartialShipmentViolation(array $violations, int $id): array
    {
        $message   = sprintf('The delivery date of shipment with id %d is not selected.', $id);
        $violation = new ConstraintViolation(
            $message,
            $message,
            [],
            $id,
            'shipments',
            null,
        );

        $violations[] = $violation;

        return $violations;
    }
}
