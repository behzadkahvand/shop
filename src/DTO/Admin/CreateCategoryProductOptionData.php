<?php

namespace App\DTO\Admin;

use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;

class CreateCategoryProductOptionData
{
    protected Category $category;

    protected ArrayCollection $options;

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return CreateCategoryProductOptionData
     */
    public function setCategory(Category $category): CreateCategoryProductOptionData
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOptions(): ArrayCollection
    {
        return $this->options;
    }

    /**
     * @param ArrayCollection $options
     * @return CreateCategoryProductOptionData
     */
    public function setOptions(ArrayCollection $options): CreateCategoryProductOptionData
    {
        $this->options = $options;
        return $this;
    }
}
