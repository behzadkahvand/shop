<?php

namespace App\Entity;

use App\Repository\ApologyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApologyRepository::class)
 */
class Apology
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"apology.read"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Promotion::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"apology.read"})
     */
    private $promotion;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"apology.read"})
     */
    private $codePrefix;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"apology.read"})
     */
    private $messageTemplate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getCodePrefix(): ?string
    {
        return $this->codePrefix;
    }

    public function setCodePrefix(string $codePrefix): self
    {
        $this->codePrefix = $codePrefix;

        return $this;
    }

    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }

    public function setMessageTemplate(string $messageTemplate): self
    {
        $this->messageTemplate = $messageTemplate;

        return $this;
    }

    public function update(array $data)
    {
        if (isset($data['codePrefix']) && !empty($data['codePrefix'])) {
            $this->setCodePrefix($data['codePrefix']);
        }

        if (isset($data['messageTemplate']) && !empty($data['messageTemplate'])) {
            $this->setMessageTemplate($data['messageTemplate']);
        }

        if (isset($data['promotion']) && $data['promotion'] instanceof Promotion) {
            $this->setPromotion($data['promotion']);
        }
    }
}
