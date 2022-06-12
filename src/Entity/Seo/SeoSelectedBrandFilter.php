<?php

namespace App\Entity\Seo;

use App\Entity\Brand;
use App\Repository\Seo\SeoSelectedBrandFilterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=SeoSelectedBrandFilterRepository::class)
 */
class SeoSelectedBrandFilter extends SeoSelectedFilter
{
    /**
     * @SerializedName("brand")
     * @ORM\ManyToOne(targetEntity=Brand::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $entity;

    public function getEntity(): Brand
    {
        return $this->entity;
    }

    public function setEntity(Brand $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
