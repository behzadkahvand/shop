<?php

namespace App\Service\ORM\CustomFilters\Category\Admin;

use App\Repository\CategoryRepository;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IsLeafCategoryCustomFilter
 */
final class IsLeafCategoryCustomFilter implements CustomFilterInterface
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * IsLeafCategoryCustomFilter constructor.
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

        if (!isset($queryParams['filter']['isLeaf'])) {
            return;
        }

        $operator = (bool) $queryParams['filter']['isLeaf'] ? 'in' : 'nin';

        $queryParams['filter']['id'][$operator] = implode(',', $this->categoryRepository->getCategoryLeafIds());

        unset($queryParams['filter']['isLeaf']);

        $request->query->replace($queryParams);
    }
}
