<?php

namespace App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\DTO\Search\SearchableCategory;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\DoctrineSearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Seo\SeoMetaDataCalculator;
use App\Service\Utils\Pagination\Pagination;

/**
 * Class CategorySearchMetaResolver
 */
final class CategorySearchMetaResolver implements DoctrineSearchMetaResolverInterface
{
    private CategoryRepository $categoryRepository;

    private SeoMetaDataCalculator $seoMetaDataCalculator;

    public function __construct(CategoryRepository $categoryRepository, SeoMetaDataCalculator $seoMetaDataCalculator)
    {
        $this->categoryRepository    = $categoryRepository;
        $this->seoMetaDataCalculator = $seoMetaDataCalculator;
    }

    /**
     * @inheritDoc
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array
    {
        if (!$data instanceof DoctrineSearchData) {
            return [];
        }

        $seoMetaData  = $this->seoMetaDataCalculator->getData($data);
        $category     = $seoMetaData->getCategory();
        $categoryPath = $category ? $this->categoryRepository->getPath($category) : [];

        $pageTitle       = $seoMetaData->getTitle();
        $description     = $seoMetaData->getDescription();
        $metaDescription = $seoMetaData->getMetaDescription();

        return [
            'categories' => [
                'pageTitle'       => $pageTitle,
                'description'     => $description,
                'metaDescription' => $metaDescription,
                'breadcrumb'      => $this->getCategoryBreadcrumb($categoryPath),
                'hierarchy'       => array_map(
                    [SearchableCategory::class, 'fromChildrenHierarchy'],
                    $this->getCategoryHierarchy($categoryPath, $category)
                ),
            ],
        ];
    }

    /**
     * @param string|null $categoryCode
     *
     * @return Category|null
     */
    private function findCategoryByCode(?string $categoryCode): ?Category
    {
        return $categoryCode ? $this->categoryRepository->findOneBy(['code' => $categoryCode]) : null;
    }

    /**
     * @param array $categoryBreadcrumb
     *
     * @return array
     */
    private function getCategoryBreadcrumb(array $categoryBreadcrumb): array
    {
        return collect($categoryBreadcrumb)->map(
            function (Category $category) {
                return [
                    'id'    => $category->getId(),
                    'code'  => $category->getCode(),
                    'title' => $category->getTitle(),
                ];
            }
        )->toArray();
    }

    /**
     * @param array $categoryPath
     * @param Category|null $category
     *
     * @return array
     */
    private function getCategoryHierarchy(array $categoryPath, ?Category $category): array
    {
        if (!$category) {
            return $this->hierarchy($this->categoryRepository->getRootCategories());
        }

        if (!$category->isLeaf()) {
            $categoryPath = $this->categoryRepository->getChildren(
                $category,
                true,
                null,
                'ASC',
                true
            );
        } else {
            $categoryPath = array_merge(
                $categoryPath,
                $this->categoryRepository->getCategorySiblings($category)
            );
        }

        usort($categoryPath, fn($a, $b): int => $a->getLevel() > $b->getLevel());

        return $this->hierarchy($categoryPath);
    }

    /**
     * @param array $nodes
     * @param array $visitedNodeIds
     *
     * @return array
     */
    private function hierarchy(array $nodes, array &$visitedNodeIds = []): array
    {
        $hierarchy = [];

        foreach ($nodes as $i => $node) {
            $nodeId = $node->getId();

            if (in_array($nodeId, $visitedNodeIds)) {
                continue;
            }

            $item = [
                'id'         => $nodeId,
                'code'       => $node->getCode(),
                'title'      => $node->getTitle(),
                'level'      => $node->getLevel(),
                '__children' => [],
            ];

            if (isset($nodes[$i + 1]) && $item['level'] < $nodes[$i + 1]->getLevel()) {
                $childNodes = array_values(
                    array_filter(
                        $nodes,
                        function ($c) use ($item) {
                            return $c->getLevel() > $item['level'];
                        }
                    )
                );

                $item['__children'] = $this->hierarchy($childNodes, $visitedNodeIds);
            }

            $hierarchy[]      = $item;
            $visitedNodeIds[] = $nodeId;
        }

        return $hierarchy;
    }
}
