<?php

namespace App\Entity;

use App\Repository\ReturnReasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;

/**
 * @ORM\Table(name="return_reasons")
 * @ORM\Entity(repositoryClass=ReturnReasonRepository::class)
 */
class ReturnReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_reason.show", "return_request.show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"return_reason.show", "return_request.show"})
     */
    private $reason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
