<?php

namespace App\Entity;

use App\Repository\ProvinceZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="province_zones")
 * @ORM\Entity(repositoryClass=ProvinceZoneRepository::class)
 */
class ProvinceZone extends Zone
{
    /**
     * @ORM\ManyToMany(targetEntity=Province::class, cascade={"persist"})
     * @Groups({"default"})
     */
    private $provinces;

    public function __construct()
    {
        parent::__construct();

        $this->provinces = new ArrayCollection();
    }

    /**
     * @return Collection|Province[]
     */
    public function getProvinces(): Collection
    {
        return $this->provinces;
    }

    public function addProvince(Province $province): self
    {
        if (!$this->provinces->contains($province)) {
            $this->provinces[] = $province;
        }

        return $this;
    }

    public function removeProvince(Province $province): self
    {
        if ($this->provinces->contains($province)) {
            $this->provinces->removeElement($province);
        }

        return $this;
    }
}
