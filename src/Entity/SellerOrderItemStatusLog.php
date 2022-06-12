<?php

namespace App\Entity;

use App\Repository\SellerOrderItemStatusLogRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @ORM\Table(name="seller_order_item_status_logs")
 * @ORM\Entity(repositoryClass=SellerOrderItemStatusLogRepository::class, readOnly=true)
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="user_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "admin" = AdminUserSellerOrderItemStatusLog::class,
 *     "seller" = SellerUserSellerOrderItemStatusLog::class,
 *     "customer" = CustomerUserSellerOrderItemStatusLog::class
 * })
 *
 * @DiscriminatorMap(typeProperty="userType", mapping={
 *    "admin"="App\Entity\AdminUserSellerOrderItemStatusLog",
 *    "seller"="App\Entity\SellerUserSellerOrderItemStatusLog",
 *     "customer"="App\Entity\CustomerUserSellerOrderItemStatusLog"
 * })
 */
abstract class SellerOrderItemStatusLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SellerOrderItem::class, inversedBy="sellerOrderItemStatusLogs")
     */
    private $sellerOrderItem;

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

    public function getSellerOrderItem(): ?SellerOrderItem
    {
        return $this->sellerOrderItem;
    }

    public function setSellerOrderItem(?SellerOrderItem $sellerOrderItem): self
    {
        $this->sellerOrderItem = $sellerOrderItem;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }
}
