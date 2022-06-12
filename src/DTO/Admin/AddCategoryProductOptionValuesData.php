<?php

namespace App\DTO\Admin;

use App\Entity\CategoryProductOption;
use Doctrine\Common\Collections\ArrayCollection;

class AddCategoryProductOptionValuesData
{
    protected CategoryProductOption $categoryProductOption;

    protected ArrayCollection $optionValues;

    /**
     * @return CategoryProductOption
     */
    public function getCategoryProductOption(): CategoryProductOption
    {
        return $this->categoryProductOption;
    }

    /**
     * @param CategoryProductOption $categoryProductOption
     * @return AddCategoryProductOptionValuesData
     */
    public function setCategoryProductOption(CategoryProductOption $categoryProductOption): AddCategoryProductOptionValuesData
    {
        $this->categoryProductOption = $categoryProductOption;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOptionValues(): ArrayCollection
    {
        return $this->optionValues;
    }

    /**
     * @param ArrayCollection $optionValues
     * @return AddCategoryProductOptionValuesData
     */
    public function setOptionValues(ArrayCollection $optionValues): AddCategoryProductOptionValuesData
    {
        $this->optionValues = $optionValues;
        return $this;
    }
}
