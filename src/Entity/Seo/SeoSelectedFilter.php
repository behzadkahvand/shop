<?php

namespace App\Entity\Seo;

use App\Entity\Category;
use App\Entity\Common\Timestampable;
use App\Repository\Seo\SeoSelectedFilterRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SeoSelectedFilterRepository::class)
 * @ORM\Table(name="seo_selected_filters", uniqueConstraints={
 *     @UniqueConstraint(name="seo_selected_filter", columns={"category_id", "entity_name", "entity_id"})
 * }))
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entity_name", type="string")
 * @ORM\DiscriminatorMap({
 *     "brand" = SeoSelectedBrandFilter::class,
 * })
 */
abstract class SeoSelectedFilter
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     * })
     */
    private $starred;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getStarred(): ?bool
    {
        return $this->starred;
    }

    public function setStarred(bool $starred): self
    {
        $this->starred = $starred;

        return $this;
    }
}
