<?php

namespace App\Service\ShippingCategory;

use App\DTO\Admin\ShippingCategorySuggestionData;
use App\Entity\ShippingCategory;
use App\Repository\ShippingCategoryRepository;

class ShippingCategorySuggestionService
{
    protected CalculateShippingCategoryNameService $calculateShippingCategoryName;

    protected ShippingCategoryRepository $shippingCategoryRepository;

    public function __construct(
        CalculateShippingCategoryNameService $calculateShippingCategoryName,
        ShippingCategoryRepository $shippingCategoryRepository
    ) {
        $this->calculateShippingCategoryName = $calculateShippingCategoryName;
        $this->shippingCategoryRepository = $shippingCategoryRepository;
    }

    public function get(ShippingCategorySuggestionData $suggestionData): ShippingCategory
    {
        $shippingCategoryName = $this->calculateShippingCategoryName->calculate(
            $suggestionData->getWeight(),
            $suggestionData->getLength(),
            $suggestionData->getWidth(),
            $suggestionData->getHeight()
        );

        return $this->shippingCategoryRepository->findOneBy(['name' => $shippingCategoryName]);
    }
}
