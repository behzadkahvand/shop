<?php

namespace App\Entity;

use App\Repository\OrderShipmentStatusLogRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="order_shipment_status_logs")
 * @ORM\Entity(repositoryClass=OrderShipmentStatusLogRepository::class, readOnly=true)
 */
class OrderShipmentStatusLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"orderShipment.show"})
     */
    private $statusFrom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"orderShipment.show"})
     */
    private $statusTo;

    /**
     * @ORM\ManyToOne(targetEntity=OrderShipment::class, inversedBy="orderShipmentStatusLogs")
     */
    private $orderShipment;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="orderShipmentStatusLogs")
     * @Groups({"orderShipment.show"})
     */
    private $user;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"orderShipment.show"})
     */
    protected $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusFrom(): ?string
    {
        return $this->statusFrom;
    }

    public function setStatusFrom(string $statusFrom): self
    {
        $this->statusFrom = $statusFrom;

        return $this;
    }

    public function getStatusTo(): ?string
    {
        return $this->statusTo;
    }

    public function setStatusTo(string $statusTo): self
    {
        $this->statusTo = $statusTo;

        return $this;
    }

    public function getOrderShipment(): ?OrderShipment
    {
        return $this->orderShipment;
    }

    public function setOrderShipment(?OrderShipment $orderShipment): self
    {
        $this->orderShipment = $orderShipment;

        return $this;
    }

    public function getUser(): ?Admin
    {
        return $this->user;
    }

    public function setUser(?Admin $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }
}
