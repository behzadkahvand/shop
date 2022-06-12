<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryClosure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;

class CategoryRepository extends ClosureTreeRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Category::class));
    }

    public function getCategoryLeafIds()
    {
        $query = $this->getLeafCategoriesQuery();

        return array_map('current', $this->_em->createQuery($query)->getResult());
    }

    public function getCategoryLeafIdsForCategory(Category $category): string
    {
        $subQuery = $this->getLeafCategoriesQuery();

        $qb = $this->getChildrenQueryBuilder($category);
        $categoryChildren = $qb->andWhere('node.id <> :parentId')
            ->andWhere("node.id IN ($subQuery)")
            ->select('GROUP_CONCAT(node.id) as ids')
            ->setParameter('parentId', $category->getId())
            ->getQuery()
            ->getScalarResult();

        return (string) ($categoryChildren[0]['ids'] ?? $category->getId());
    }

    public function getCategoryLeafIdsForCategoryIds(string ...$ids): array
    {
        $categories = $this->findBy([
            'id' => $ids
        ]);

        $leafIds = [];
        foreach ($categories as $category) {
            array_push(
                $leafIds,
                ...array_map(
                    'intval',
                    explode(',', $this->getCategoryLeafIdsForCategory($category))
                )
            );
        }

        return array_unique($leafIds);
    }

    public function getRootCategories(): array
    {
        return $this->findBy(['level' => 1]);
    }

    public function getCategorySiblings(Category $category): array
    {
        $parent = $category->getParent();

        if (! $parent) {
            return [];
        }

        return array_map(
            fn (CategoryClosure $c) => $c->getDescendant(),
            $this->getChildrenQueryBuilder($parent, true)
                ->andWhere('node.id <> :category')
                ->setParameter('category', $category)
                ->getQuery()
                ->getResult()
        );
    }

    public function getCategoryIdsFromItemCollection(Collection $itemCollection)
    {
        $inventoryCategoryMap = [];
        // TODO use query builder for better performance
        $itemCollection->forAll(function ($key, $item) use (&$inventoryCategoryMap) {
            $categoryId = $item->getInventory()->getVariant()->getProduct()->getCategory()->getId();
            $inventoryId = $item->getInventory()->getId();

            if (!isset($inventoryCategoryMap[$categoryId])) {
                $inventoryCategoryMap[$categoryId] = [];
            }
            $inventoryCategoryMap[$categoryId][] = $inventoryId;

            return true;
        });

        return $inventoryCategoryMap;
    }

    private function getLeafCategoriesQuery(): string
    {
        $subQuery = 'SELECT IDENTITY(cc.ancestor) ';
        $subQuery .= 'FROM App\\Entity\\CategoryClosure as cc ';
        $subQuery .= 'GROUP BY cc.ancestor HAVING COUNT(cc.descendant) = 1';

        return $subQuery;
    }

    public function findLeafCategoriesByProductTitleQueryBuilder(string $title, int $limit): QueryBuilder
    {
        $leafCats = $this->getLeafCategoriesQuery();

        return $this->createQueryBuilder('category')
                    ->innerJoin('category.products', 'product')
                    ->where("category.id IN ({$leafCats})")
                    ->andWhere('product.title LIKE :title')
                    ->orWhere('product.subtitle LIKE :title')
                    ->orWhere('product.additionalTitle LIKE :title')
                    ->setMaxResults($limit)
                    ->setParameter('title', "%{$title}%");
    }

    public function findLeafCategoriesByProductTitle(string $title, int $limit)
    {
        return $this->findLeafCategoriesByProductTitleQueryBuilder($title, $limit)->getQuery()->getResult();
    }

    public function getReferenceByCodes(array $categoryCodes): iterable
    {
        $qb = $this->createQueryBuilder('category')
                   ->select('category.id')
                   ->where('category.code IN (:categoryCodes)')
                   ->setParameters(compact('categoryCodes'));

        $ids = array_column($qb->getQuery()->getScalarResult(), 'id');

        foreach ($ids as $id) {
            yield $this->_em->getReference($this->_entityName, $id);
        }
    }

    public function getLikeBySearchQuery(string $searchQuery, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('category');

        return $queryBuilder->select('category.title', 'category.code')
            ->where($queryBuilder->expr()->like('category.title', ':title'))
            ->setParameter('title', '%' . $searchQuery . '%')
            ->orderBy('category.level', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllIds(): array
    {
        $result = $this->createQueryBuilder('category')
            ->select('category.id')
            ->getQuery()
            ->getResult();

        return array_column($result, 'id');
    }

    public function findLeafCategoryByTitle(string $title): ?Category
    {
        $leafCats = $this->getLeafCategoriesQuery();

        $result = $this->createQueryBuilder('category')
            ->where("category.id IN ({$leafCats})")
            ->andWhere('category.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getResult();

        return !empty($result) ? $result[0] : null;
    }

    public function findOneRandomLeafCategory(): ?Category
    {
        $leafCats = $this->getLeafCategoriesQuery();

        $result = $this->createQueryBuilder('category')
            ->where("category.id IN ({$leafCats})")
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return !empty($result) ? $result[0] : null;
    }
}
