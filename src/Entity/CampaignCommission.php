<?php

namespace App\Entity;

use App\Repository\CampaignCommissionRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use OpenApi\Annotations as OA;

/**
 * @ORM\Table(name="campaign_commissions")
 * @ORM\Entity(repositoryClass=CampaignCommissionRepository::class)
 */
class CampaignCommission
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"campaignCommission.show"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"campaignCommission.show"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"campaignCommission.show"})
     */
    private $brand;
    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"campaignCommission.show"})
     *
     */
    private $seller;

    /**
     * @var DateTime
     * @ORM\Column(type="date")
     *
     * @OA\Property(example="2018-04-16 11:11:11"),
     * @Groups({"campaignCommission.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private $startDate;

    /**
     * @var DateTime
     * @ORM\Column(type="date")
     *
     * @OA\Property(example="2018-04-16 11:11:11"),
     * @Groups({"campaignCommission.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private $endDate;

    /**
     * @ORM\Column(type="float")
     *
     * @Groups({"campaignCommission.show"})
     */
    private $fee;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     *
     * @OA\Property(example="2018-04-16 11:11:11"),
     * @Groups({"campaignCommission.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    protected $createdAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @OA\Property(example="2018-04-16 11:11:11"),
     * @Groups({"campaignCommission.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    protected $terminatedAt;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="create")
     *
     * @Groups({"campaignCommission.show"})
     */
    protected $createdBy;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="update")
     *
     * @Groups({"campaignCommission.show"})
     */
    protected $updatedBy;

    /**
     * @var ?DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @OA\Property(example="2018-04-16 11:11:11"),
     * @Groups({"campaignCommission.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    protected $updatedAt;

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setBrand(Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function setSeller(Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function setStartDate(DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function setEndDate(DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function setFee(float $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setTerminatedAt(DateTime $terminatedAt): self
    {
        $this->terminatedAt = $terminatedAt;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getTerminatedAt(): ?DateTime
    {
        return $this->terminatedAt;
    }

    public function terminate(): void
    {
        $this->terminatedAt = new DateTime();
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
