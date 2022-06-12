<?php

namespace App\Entity;

use App\Repository\GenericZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="generic_zones")
 * @ORM\Entity(repositoryClass=GenericZoneRepository::class)
 */
class GenericZone extends Zone
{
    /**
     * @ORM\ManyToMany(targetEntity=Zone::class)
     * @Groups({"default"})
     */
    private $members;

    public function __construct()
    {
        parent::__construct();

        $this->members = new ArrayCollection();
    }

    /**
     * @return Collection|Zone[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Zone $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(Zone $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }
}
