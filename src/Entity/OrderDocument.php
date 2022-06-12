<?php

namespace App\Entity;

use App\Dictionary\OrderDocumentType;
use App\Repository\OrderDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_documents")
 * @ORM\Entity(repositoryClass=OrderDocumentRepository::class)
 */
class OrderDocument extends Document
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(OrderDocumentType::DEPOSIT);
    }

    /**
     * @ORM\OneToOne(targetEntity=Order::class, mappedBy="orderDocument", cascade={"persist", "remove"})
     */
    private $order;

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        // set the owning side of the relation if necessary
        if ($order->getOrderDocument() !== $this) {
            $order->setOrderDocument($this);
        }

        return $this;
    }
}
