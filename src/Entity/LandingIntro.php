<?php

namespace App\Entity;

use App\Repository\LandingIntroRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=LandingIntroRepository::class)
 *
 * @UniqueEntity(
 *     fields={"mobile"},
 *     errorPath="mobile",
 *     message="This mobile number is already exists.",
 *     groups={"landing.intro.store"}
 * )
 */
class LandingIntro
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     *
     * @Assert\NotBlank(groups={"landing.intro.store"})
     * @AppAssert\Mobile(groups={"landing.intro.store"})
     *
     * @Groups({"landing.intro.store"})
     */
    private $mobile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }
}
