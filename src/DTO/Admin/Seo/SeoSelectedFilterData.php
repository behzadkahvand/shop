<?php

namespace App\DTO\Admin\Seo;

use App\Entity\Brand;
use App\Entity\Category;

class SeoSelectedFilterData
{
    protected Category $category;

    protected Brand $brand;

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $metaDescription = null;

    protected bool $starred;

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return SeoSelectedFilterData
     */
    public function setCategory(Category $category): SeoSelectedFilterData
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     * @return SeoSelectedFilterData
     */
    public function setBrand(Brand $brand): SeoSelectedFilterData
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return SeoSelectedFilterData
     */
    public function setTitle(?string $title): SeoSelectedFilterData
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return SeoSelectedFilterData
     */
    public function setDescription(?string $description): SeoSelectedFilterData
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     * @return SeoSelectedFilterData
     */
    public function setMetaDescription(?string $metaDescription): SeoSelectedFilterData
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStarred(): bool
    {
        return $this->starred;
    }

    /**
     * @param bool $starred
     * @return SeoSelectedFilterData
     */
    public function setStarred(bool $starred): SeoSelectedFilterData
    {
        $this->starred = $starred;
        return $this;
    }

    public function getEntity()
    {
        return $this->getBrand();
    }
}
