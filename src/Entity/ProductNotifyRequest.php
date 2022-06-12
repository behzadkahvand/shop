<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\ProductNotifyRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_notify_requests")
 * @ORM\Entity(repositoryClass=ProductNotifyRequestRepository::class)
 */
class ProductNotifyRequest
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"default","notify.read",})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default","notify.read",})
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="productNotifyRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
