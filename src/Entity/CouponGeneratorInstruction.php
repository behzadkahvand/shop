<?php

namespace App\Entity;

use App\Dictionary\CouponGeneratorInstructionStatus;
use App\Repository\CouponGeneratorInstructionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Promotion\CouponGeneratorInstruction as ValidCouponGeneratorInstruction;

/**
 * @ORM\Entity(repositoryClass=CouponGeneratorInstructionRepository::class)
 *
 * @ORM\Table(
 *     indexes={@ORM\Index(
 *         name="prefix__code_length__suffix_status",
 *         columns={"prefix", "code_length", "suffix", "status"}
 *     )}
 * )
 *
 * @ValidCouponGeneratorInstruction(groups={"coupon_generation_instruction.create"})
 */
class CouponGeneratorInstruction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Range(min=1, groups={"Default", "coupon_generation_instruction.create"})
     * @Assert\NotBlank(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=127)
     *
     * @Assert\Length(max=127, groups={"Default", "coupon_generation_instruction.create"})
     * @Assert\NotNull(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $prefix = "";

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Range(min=1, groups={"Default", "coupon_generation_instruction.create"})
     * @Assert\NotBlank(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $codeLength;

    /**
     * @ORM\Column(type="string", length=127)
     *
     * @Assert\Length(max=127, groups={"Default", "coupon_generation_instruction.create"})
     * @Assert\NotNull(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $suffix = "";

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $status = CouponGeneratorInstructionStatus::PENDING;

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(groups={"Default", "coupon_generation_instruction.create"})
     *
     * @Groups({"couponGenerationInstruction.read"})
     */
    private $promotion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getCodeLength(): ?int
    {
        return $this->codeLength;
    }

    public function setCodeLength(int $codeLength): self
    {
        $this->codeLength = $codeLength;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }
}
