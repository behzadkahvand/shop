<?php

namespace App\Service\Product\Search\Utils\Seo\ValueObjects;

use App\Entity\Category;

class SeoMetaDataValueObject
{
    protected ?Category $category = null;

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $metaDescription = null;

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return SeoMetaDataValueObject
     */
    public function setCategory(?Category $category): SeoMetaDataValueObject
    {
        $this->category = $category;
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
     * @return SeoMetaDataValueObject
     */
    public function setTitle(?string $title): SeoMetaDataValueObject
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
     * @return SeoMetaDataValueObject
     */
    public function setDescription(?string $description): SeoMetaDataValueObject
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
     * @return SeoMetaDataValueObject
     */
    public function setMetaDescription(?string $metaDescription): SeoMetaDataValueObject
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }
}
