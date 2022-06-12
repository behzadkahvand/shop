<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\ConsultationRequestRepository;
use App\Validator\Mobile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="consultation_requests")
 * @ORM\Entity(repositoryClass=ConsultationRequestRepository::class)
 * @UniqueEntity(
 *     fields={"phone"},
 *     errorPath="phone",
 *     message="This phone number is already exists."
 * )
 */
class ConsultationRequest
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Mobile()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $organization;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}
