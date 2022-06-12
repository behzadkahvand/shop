<?php

namespace App\DTO\Search;

use App\Entity\Category;

/**
 * Class SearchableCategory
 */
final class SearchableCategory implements \JsonSerializable
{
    private int $id;

    private string $code;

    private string $title;

    private int $level;

    private bool $hasChildren;

    private array $children;

    /**
     * SearchableCategory constructor.
     */
    public function __construct(array $node)
    {
        $this->id          = $node['id'];
        $this->code        = $node['code'];
        $this->title       = $node['title'];
        $this->level       = $node['level'];
        $this->hasChildren = count($node['__children']) > 0;
        $this->children    = count($node['__children'])
            ? array_map([self::class, 'fromChildrenHierarchy'], $node['__children'])
            : [];
    }

    /**
     * @param array $node
     *
     * @return SearchableCategory
     */
    public static function fromChildrenHierarchy(array $node): SearchableCategory
    {
        return new self($node);
    }

    /**
     * @param Category $category
     *
     * @return SearchableCategory
     */
    public static function fromCategory(Category $category): SearchableCategory
    {
        $node               = [];
        $node['id']         = $category->getId();
        $node['code']       = $category->getCode();
        $node['title']      = $category->getTitle();
        $node['level']      = 0;
        $node['__children'] = [];

        return self::fromChildrenHierarchy($node);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
