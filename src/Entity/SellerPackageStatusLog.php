<?php

namespace App\Entity;

use App\Repository\SellerPackageStatusLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SellerPackageStatusLogRepository::class)
 * @ORM\Table(name="seller_package_status_logs")
 */
class SellerPackageStatusLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $statusFrom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $statusTo;

    /**
     * @var SellerPackage
     *
     * @ORM\ManyToOne(targetEntity=SellerPackage::class, inversedBy="statusLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sellerPackage;

    /**
     * @var Admin|null
     * @ORM\ManyToOne(targetEntity=Admin::class)
     */
    private $user;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * SellerPackageStatusLog constructor.
     *
     * @param string        $statusFrom
     * @param string        $statusTo
     * @param SellerPackage $sellerPackage
     * @param Admin|null    $user
     */
    public function __construct(string $statusFrom = null, string $statusTo = null, SellerPackage $sellerPackage = null, Admin $user = null)
    {
        $this->statusFrom    = $statusFrom;
        $this->statusTo      = $statusTo;
        $this->sellerPackage = $sellerPackage;
        $this->user          = $user;
        $this->createdAt     = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatusFrom(): string
    {
        return $this->statusFrom;
    }

    /**
     * @param string $statusFrom
     *
     * @return SellerPackageStatusLog
     */
    public function setStatusFrom(string $statusFrom): SellerPackageStatusLog
    {
        $this->statusFrom = $statusFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusTo(): string
    {
        return $this->statusTo;
    }

    /**
     * @param string $statusTo
     *
     * @return SellerPackageStatusLog
     */
    public function setStatusTo(string $statusTo): SellerPackageStatusLog
    {
        $this->statusTo = $statusTo;

        return $this;
    }

    /**
     * @return SellerPackage
     */
    public function getSellerPackage(): SellerPackage
    {
        return $this->sellerPackage;
    }

    /**
     * @param SellerPackage $sellerPackage
     *
     * @return SellerPackageStatusLog
     */
    public function setSellerPackage(SellerPackage $sellerPackage): SellerPackageStatusLog
    {
        $this->sellerPackage = $sellerPackage;

        return $this;
    }

    /**
     * @return Admin|null
     */
    public function getUser(): ?Admin
    {
        return $this->user;
    }

    /**
     * @param Admin|null $user
     *
     * @return SellerPackageStatusLog
     */
    public function setUser(?Admin $user): SellerPackageStatusLog
    {
        $this->user = $user;

        return $this;
    }
}
