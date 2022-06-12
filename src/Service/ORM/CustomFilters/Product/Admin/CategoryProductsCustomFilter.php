<?php

namespace App\Service\ORM\CustomFilters\Product\Admin;

use App\Repository\CategoryRepository;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryProductsCustomFilter
 */
final class CategoryProductsCustomFilter implements CustomFilterInterface
{
    private CategoryRepository $categoryRepository;

    /**
     * CategoryProductsCustomFilter constructor.
     *
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();
        $categoryId    = $queryParams['filter']['category'] ?? null;

        if (null === $categoryId) {
            return;
        }

        unset($queryParams['filter']['category']);

        if (is_array($categoryId)) {
            $categoryId = current($categoryId);
        }

        $category = $category = $this->categoryRepository->find($categoryId);

        if (null === $category) {
            $leafIds = '-1'; // invalid category id => no products found
        } elseif ($category->isLeaf()) {
            $leafIds = (string) $categoryId;
        } else {
            $leafIds = $this->categoryRepository->getCategoryLeafIdsForCategory($category);
        }

        $request->query->replace(array_replace_recursive($queryParams, [
            'filter' => [
                'category.id' => [
                    'in' => $leafIds,
                ],
            ],
        ]));
    }
}
