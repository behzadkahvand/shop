<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\Inventory;

/**
 * Class SelectedPartialShipment
 */
final class SelectedPartialShipment
{
    /**
     * @var array|Inventory[]
     */
    private ?array $inventories;

    /**
     * @var \DateTimeInterface|null
     */
    private ?\DateTimeInterface $selectedDeliveryDatetime;

    /**
     * SelectedPartialShipment constructor.
     *
     * @param array|null $inventories
     * @param \DateTimeInterface|null $selectedDeliveryDatetime
     */
    public function __construct(?array $inventories, ?\DateTimeInterface $selectedDeliveryDatetime)
    {
        $this->inventories              = $inventories;
        $this->selectedDeliveryDatetime = $selectedDeliveryDatetime;
    }

    /**
     * @return Inventory[]|array
     */
    public function getInventories(): ?array
    {
        return $this->inventories;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getSelectedDeliveryDatetime(): ?\DateTimeInterface
    {
        return $this->selectedDeliveryDatetime;
    }
}
