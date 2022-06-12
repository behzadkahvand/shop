<?php

namespace App\Entity;

use App\Repository\OrderStatusLogRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="order_status_logs")
 * @ORM\Entity(repositoryClass=OrderStatusLogRepository::class, readOnly=true)
 */
class OrderStatusLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"order.show"})
     */
    private $statusFrom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"order.show"})
     */
    private $statusTo;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderStatusLogs")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="orderStatusLogs")
     * @Groups({"order.show"})
     */
    private $user;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"order.show"})
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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

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
