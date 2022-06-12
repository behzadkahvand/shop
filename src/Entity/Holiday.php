<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\HolidayRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="holidays")
 * @ORM\Entity(repositoryClass=HolidayRepository::class)
 *
 * @UniqueEntity(
 *     fields={"seller","date","supply"},
 *     errorPath="seller",
 *     message="This record is already exists.",
 *     groups={"holiday.create","holiday.update"}
 * )
 */
class Holiday
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="holidays")
     *
     * @Assert\NotBlank(groups={"seller.holiday.store"})
     *
     * @Groups({"default"})
     */
    private $seller;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"holiday.create","holiday.update"})
     * @Groups({"default"})
     */
    private $title;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(groups={"holiday.create","holiday.update"})
     * @Groups({"default"})
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool",groups={"holiday.create","holiday.update"})
     * @Groups({"default"})
     */
    private $supply = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isSupply(): ?bool
    {
        return $this->supply;
    }

    public function setSupply(?bool $supply): self
    {
        $this->supply = $supply;

        return $this;
    }
}
