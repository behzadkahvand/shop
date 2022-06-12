<?php

namespace App\DTO\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateShipmentData
 */
final class CreateShipmentData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTimeInterface|null
     *
     * @Assert\NotBlank()
     */
    private $deliveryDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return CreateShipmentData
     */
    public function setId(int $id): CreateShipmentData
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTimeInterface|null $deliveryDate
     *
     * @return CreateShipmentData
     */
    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }
}
