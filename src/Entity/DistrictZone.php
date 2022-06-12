<?php

namespace App\Entity;

use App\Repository\DistrictZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="district_zones")
 * @ORM\Entity(repositoryClass=DistrictZoneRepository::class)
 */
class DistrictZone extends Zone
{
    /**
     * @ORM\ManyToMany(targetEntity=District::class)
     * @Groups({"default"})
     */
    private $districts;

    public function __construct()
    {
        parent::__construct();

        $this->districts = new ArrayCollection();
    }

    /**
     * @return Collection|District[]
     */
    public function getDistricts(): Collection
    {
        return $this->districts;
    }

    public function addDistrict(District $district): self
    {
        if (!$this->districts->contains($district)) {
            $this->districts[] = $district;
        }

        return $this;
    }

    public function removeDistrict(District $district): self
    {
        if ($this->districts->contains($district)) {
            $this->districts->removeElement($district);
        }

        return $this;
    }
}
