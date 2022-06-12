<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="category_deliveries")
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryRepository", repositoryClass=DeliveryRepository::class)
 */
class Delivery
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"category.delivery.index", "category.delivery.show", "category.delivery.store", "category.delivery.update"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(groups={"category.delivery.store", "category.delivery.update"})
     * @Assert\NotNull(groups={"category.delivery.store", "category.delivery.update"})
     *
     * @Groups({"category.delivery.index", "category.delivery.show", "category.delivery.store", "category.delivery.update"})
     */
    private $start;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(groups={"category.delivery.store", "category.delivery.update"})
     * @Assert\NotNull(groups={"category.delivery.store", "category.delivery.update"})
     *
     * @Groups({"category.delivery.index", "category.delivery.show", "category.delivery.store", "category.delivery.update"})
     */
    private $end;

    /**
     * @ORM\OneToOne(targetEntity=ShippingCategory::class, mappedBy="delivery")
     */
    private $shippingCategory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(?int $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function setEnd(?int $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getShippingCategory()
    {
        return $this->shippingCategory;
    }

    public function setShippingCategory(ShippingCategory $shippingCategory): self
    {
        $this->shippingCategory = $shippingCategory;

        return $this;
    }
}
