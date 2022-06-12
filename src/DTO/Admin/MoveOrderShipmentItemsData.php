<?php

namespace App\DTO\Admin;

use App\Entity\OrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class MoveOrderShipmentItemsData
{
    /**
     * @var ArrayCollection|OrderItem[]
     *
     * @Assert\NotBlank
     * @Assert\Count(min=1)
     */
    private $items;

    /**
     * @return OrderItem[]|ArrayCollection|null
     */
    public function getItems(): ?ArrayCollection
    {
        return $this->items ?? null;
    }

    /**
     * @param iterable|array $items
     *
     * @return MoveOrderShipmentItemsData
     */
    public function setItems(ArrayCollection $items): self
    {
        $this->items = $items;

        return $this;
    }
}
