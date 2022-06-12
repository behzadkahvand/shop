<?php

namespace App\Entity;

use App\Dictionary\OrderDocumentType;
use App\Repository\RefundDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="refund_documents")
 * @ORM\Entity(repositoryClass=RefundDocumentRepository::class)
 */
class RefundDocument extends Document
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(OrderDocumentType::WITHDRAW);
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="refundDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getType(): string
    {
        return 'refund';
    }
}
