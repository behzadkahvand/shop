<?php

namespace App\Entity\Media;

use App\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ProductFeaturedImage extends Media
{
    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="featuredImage", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    public function getEntity(): ?Product
    {
        return $this->entity;
    }

    public function setEntity(?Product $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
