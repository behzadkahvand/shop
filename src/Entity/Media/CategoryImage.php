<?php

namespace App\Entity\Media;

use App\Entity\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CategoryImage extends Media
{
    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="image", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    public function getEntity(): ?Category
    {
        return $this->entity;
    }

    public function setEntity(?Category $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
