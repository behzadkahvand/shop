<?php

namespace App\Entity;

use App\Repository\CityZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="city_zones")
 * @ORM\Entity(repositoryClass=CityZoneRepository::class)
 */
class CityZone extends Zone
{
    /**
     * @ORM\ManyToMany(targetEntity=City::class)
     * @Groups({"default"})
     */
    private $cities;

    public function __construct()
    {
        parent::__construct();

        $this->cities = new ArrayCollection();
    }

    /**
     * @return Collection|City[]
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->contains($city)) {
            $this->cities->removeElement($city);
        }

        return $this;
    }
}
