<?php

namespace App\Service\Product\BreadCrumb;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use Psr\Cache\CacheItemPoolInterface;

class ProductBreadCrumbService
{
    public const CACHE_NAME = 'category_path_';

    protected CategoryRepository $categoryRepository;

    protected CacheItemPoolInterface $cache;

    public function __construct(CategoryRepository $categoryRepository, CacheItemPoolInterface $cache)
    {
        $this->categoryRepository = $categoryRepository;
        $this->cache              = $cache;
    }

    public function get(Product $product): array
    {
        $category = $product->getCategory();
        $key      = self::CACHE_NAME . $category->getId();

        $cacheItem = $this->cache->getItem($key);
        $pathData  = $cacheItem->get();

        if (!is_null($pathData)) {
            return $pathData;
        }

        $categoryPath = $this->categoryRepository->getPath($category);

        $pathData = collect($categoryPath)->map(
            function (Category $category) {
                return [
                    'id'        => $category->getId(),
                    'code'      => $category->getCode(),
                    'title'     => $category->getTitle(),
                    'image'     => $category->getImage(),
                    'pageTitle' => $category->getPageTitle(),
                    'subtitle'  => $category->getSubtitle(),
                ];
            }
        )->toArray();

        $cacheItem->set($pathData)->expiresAfter(86400);

        $this->cache->save($cacheItem);

        return $pathData;
    }
}
