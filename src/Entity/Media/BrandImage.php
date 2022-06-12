<?php

namespace App\Entity\Media;

use App\Entity\Brand;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BrandImage extends Media
{
    /**
     * @ORM\ManyToOne(targetEntity=Brand::class, inversedBy="image", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    public function getEntity(): ?Brand
    {
        return $this->entity;
    }

    public function setEntity(?Brand $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
