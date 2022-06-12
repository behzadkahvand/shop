<?php

namespace App\Entity;

use App\Repository\ReturnVerificationReasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="return_verification_reasons")
 * @ORM\Entity(repositoryClass=ReturnVerificationReasonRepository::class)
 */
class ReturnVerificationReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_verification_reason.show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"return_verification_reason.show"})
     */
    protected $reason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}
