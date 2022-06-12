<?php

namespace App\Service\Utils\Pagination;

use App\Entity\Category;

/**
 * Class BreadcrumbGenerator
 */
final class BreadcrumbGenerator
{
    public static function forCategory(Category $category): array
    {
        if (!$category->isLeaf()) {
            throw new \InvalidArgumentException(sprintf('%s() expect leaf categories', __METHOD__));
        }

        $breadcrumb = [];

        do {
            array_unshift($breadcrumb, $category);

            $category = $category->getParent();
        } while ($category !== null);

        return $breadcrumb;
    }
}
