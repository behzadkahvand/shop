<?php

namespace App\Entity;

use App\Dictionary\InventoryUpdateDemandStatus;
use App\Entity\Common\Timestampable;
use App\Repository\InventoryUpdateDemandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=InventoryUpdateDemandRepository::class)
 */
class InventoryUpdateDemand
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"seller.inventory_update_demand.list"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="inventoryUpdateDemands")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seller;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @Groups({"seller.inventory_update_demand.list"})
     */
    private string $status = InventoryUpdateDemandStatus::PENDING;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dirPath;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"seller.inventory_update_demand.list"})
     */
    private $fileName;

    /**
     * @ORM\OneToMany(targetEntity=InventoryUpdateSheet::class, mappedBy="demand", orphanRemoval=true)
     */
    private $sheets;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity=InventoryUpdateDemand::class, inversedBy="inventoryUpdateDemands")
     */
    private $demand;

    /**
     * @ORM\OneToMany(targetEntity=InventoryUpdateDemand::class, mappedBy="demand")
     */
    private $inventoryUpdateDemands;

    public function __construct()
    {
        $this->sheets = new ArrayCollection();
        $this->inventoryUpdateDemands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, InventoryUpdateDemandStatus::ALL)) {
            throw new \InvalidArgumentException();
        }

        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function getDirPath(): ?string
    {
        return $this->dirPath;
    }

    public function setDirPath(?string $dirPath): self
    {
        $this->dirPath = $dirPath;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return Collection|InventoryUpdateSheet[]
     */
    public function getSheets(): Collection
    {
        return $this->sheets;
    }

    public function addSheet(InventoryUpdateSheet $sheet): self
    {
        if (!$this->sheets->contains($sheet)) {
            $this->sheets[] = $sheet;
            $sheet->setDemand($this);
        }

        return $this;
    }

    public function removeSheet(InventoryUpdateSheet $sheet): self
    {
        if ($this->sheets->removeElement($sheet)) {
            // set the owning side to null (unless already changed)
            if ($sheet->getDemand() === $this) {
                $sheet->setDemand(null);
            }
        }

        return $this;
    }

    public function isExpired()
    {
        return $this->getExpiresAt() < new \DateTime();
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getDemand(): ?self
    {
        return $this->demand;
    }

    public function setDemand(?self $demand): self
    {
        $this->demand = $demand;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getInventoryUpdateDemands(): Collection
    {
        return $this->inventoryUpdateDemands;
    }

    public function addInventoryUpdateDemand(self $inventoryUpdateDemand): self
    {
        if (!$this->inventoryUpdateDemands->contains($inventoryUpdateDemand)) {
            $this->inventoryUpdateDemands[] = $inventoryUpdateDemand;
            $inventoryUpdateDemand->setDemand($this);
        }

        return $this;
    }

    public function removeInventoryUpdateDemand(self $inventoryUpdateDemand): self
    {
        if ($this->inventoryUpdateDemands->removeElement($inventoryUpdateDemand)) {
            // set the owning side to null (unless already changed)
            if ($inventoryUpdateDemand->getDemand() === $this) {
                $inventoryUpdateDemand->setDemand(null);
            }
        }

        return $this;
    }
}
