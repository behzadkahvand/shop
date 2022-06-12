<?php

namespace App\Entity;

use App\Repository\OrderCancelReasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_cancel_reasons")
 * @ORM\Entity(repositoryClass=OrderCancelReasonRepository::class)
 *
 * @UniqueEntity(fields={"code"}, message="This code is already used.")
 */
class OrderCancelReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"admin.order_cancel_reason.index", "admin.order_cancel_reason.show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     *
     * @Groups({"admin.order_cancel_reason.index", "admin.order_cancel_reason.show"})
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"admin.order_cancel_reason.index", "admin.order_cancel_reason.show"})
     * @Assert\NotBlank()
     */
    private $reason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
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
