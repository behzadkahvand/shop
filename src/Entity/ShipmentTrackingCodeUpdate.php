<?php

namespace App\Entity;

use App\Dictionary\ShipmentTrackingCodeStatus;
use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ShipmentTrackingCodeUpdateRepository;

/**
 * @ORM\Table(name="tracking_code_update")
 * @ORM\Entity(repositoryClass=ShipmentTrackingCodeUpdateRepository::class)
 */
class ShipmentTrackingCodeUpdate
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filePath;


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $errors = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = ShipmentTrackingCodeStatus::PENDING;


    public function getId(): int
    {
        return $this->id;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
