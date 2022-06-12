<?php

namespace App\Entity;

use App\Repository\PromotionActionRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PromotionActionRepository::class)
 */
class PromotionAction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"promotionAction.read", "promotion.read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"promotionAction.read", "promotion.read"})
     *
     * @Assert\NotBlank(groups={"promotion.create"})
     */
    private $type;

    /**
     * @ORM\Column(type="json")
     *
     * @Groups({"promotionAction.read", "promotion.read"})
     */
    private $configuration = [];

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class, inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $promotion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @OA\Property(type="object")
     *
     * @return array|null
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;

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
