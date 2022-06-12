<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="configurations")
 * @ORM\Entity(repositoryClass=ConfigurationRepository::class)
 */
class Configuration
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default", "configurations.grid"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Groups({"default", "configurations.grid"})
     */
    private $code;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotNull()
     * @Groups({"default", "configurations.grid"})
     * @Gedmo\Versioned()
     *
     * @var string
     */
    private $value;

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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
