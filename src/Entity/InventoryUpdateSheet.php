<?php

namespace App\Entity;

use App\Dictionary\InventoryUpdateSheetStatus;
use App\Entity\Common\Timestampable;
use App\Repository\InventoryUpdateSheetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=InventoryUpdateSheetRepository::class)
 */
class InventoryUpdateSheet
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=InventoryUpdateDemand::class, inversedBy="sheets")
     * @ORM\JoinColumn(nullable=false)
     */
    private InventoryUpdateDemand $demand;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $totalCount = 0;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $succeededCount = 0;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $failedCount = 0;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $status = InventoryUpdateSheetStatus::PENDING;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dirPath;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $fileName;

    /**
     * @ORM\OneToOne(targetEntity=InventoryUpdateDemand::class, cascade={"persist", "remove"})
     *
     * @Groups({"seller.inventory_update_sheet.list"})
     */
    private $fixerDemand;

    public function __construct(InventoryUpdateDemand $demand)
    {
        $this->demand = $demand;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemand(): InventoryUpdateDemand
    {
        return $this->demand;
    }

    public function setDemand(InventoryUpdateDemand $demand): self
    {
        $this->demand = $demand;

        return $this;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    public function getSucceededCount(): ?int
    {
        return $this->succeededCount;
    }

    public function setSucceededCount(int $succeededCount): self
    {
        $this->succeededCount = $succeededCount;

        return $this;
    }

    public function getFailedCount(): ?int
    {
        return $this->failedCount;
    }

    public function setFailedCount(int $failedCount): self
    {
        $this->failedCount = $failedCount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDirPath(): ?string
    {
        return $this->dirPath;
    }

    public function setDirPath(string $dirPath): self
    {
        $this->dirPath = $dirPath;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getRootDemand(): InventoryUpdateDemand
    {
        $demand = $this->getDemand();
        while ($demand->getDemand()) {
            $demand = $demand->getDemand();
        }

        return $demand;
    }

    public function getFixerDemand(): ?InventoryUpdateDemand
    {
        return $this->fixerDemand;
    }

    public function setFixerDemand(?InventoryUpdateDemand $fixerDemand): self
    {
        $this->fixerDemand = $fixerDemand;

        return $this;
    }
}
