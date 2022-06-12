<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\ProductBetterPriceReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="product_better_price_reports")
 * @ORM\Entity(repositoryClass=ProductBetterPriceReportRepository::class)
 */
class ProductBetterPriceReport
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"product.better.price.read"})
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     *
     * @Groups({"product.better.price.read"})
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"product.better.price.read"})
     */
    private $storeName;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     *
     * @Groups({"product.better.price.read"})
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"product.better.price.read"})
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getStoreName(): ?string
    {
        return $this->storeName;
    }

    public function setStoreName(?string $storeName): self
    {
        $this->storeName = $storeName;

        return $this;
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

        return $this;
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
