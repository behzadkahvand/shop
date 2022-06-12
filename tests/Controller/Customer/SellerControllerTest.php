<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seller;
use App\Repository\BrandRepository;
use App\Tests\Controller\BaseControllerTestCase;

class SellerControllerTest extends BaseControllerTestCase
{
    protected ?Brand $brand;

    protected ?Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $sellerRepo   = $this->manager->getRepository(Seller::class);
        $this->seller = $sellerRepo->findOneBy([]);

        $brandRepo   = $this->client->getContainer()->get(BrandRepository::class);
        $this->brand = $brandRepo->findOneBy([]);

        $categoryRepo = $this->manager->getRepository(Category::class);

        $categoryRepo->rebuildClosure();
        $categoryRepo->updateLevelValues();

        $this->category = $categoryRepo->findOneBy([]);
    }

    protected function tearDown(): void
    {
        unset($this->brand, $this->category);

        parent::tearDown();
    }

    public function testItCanSearchProductWithoutFiltersAndSorts(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithFilterAndSortFinalPrice(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter' => [
                        'price' => [
                            'btn' => '10000,300000',
                        ],
                    ],
                    'sort'   => [
                        '-price',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithBrandIdFilterAndNewestProductSort(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter' => [
                        'brandId' => $this->brand->getId(),
                    ],
                    'sort'   => [
                        '-latest',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithProductTitleFilterAndProductVisitsSort(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter'           => [
                        'title' => [
                            'like' => '%Ltd%',
                        ],
                    ],
                    'sort'             => [
                        '-mostVisited',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithBrandCodeAndAvailableProductFilterAndBestSellersProductSort(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter'           => [
                        'brand'     => $this->brand->getCode(),
                        'available' => true,
                    ],
                    'sort'             => [
                        '-bestSellers',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithCategoryCodeAndProductOriginalityFilterAndNoSort(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter'           => [
                        'category' => $this->category->getCode(),
                        'original' => true,
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if ($product['status'] !== ProductStatusDictionary::SOON) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testItCanSearchProductWithFastestDeliverySortAndWithoutFilter(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'sort'             => [
                        '-deliveryTime',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
        self::assertArrayHasKey('brands', $metas);
        self::assertArrayHasKey('categories', $metas);
        self::assertArrayHasKey('seller', $metas);
        self::assertArrayHasKey('name', $metas['seller']);
        self::assertArrayHasKey('createdAt', $metas['seller']);

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('pageTitle', $categories);
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        self::assertNull($categories['pageTitle']);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            foreach ($category['children'] as $child) {
                self::assertArrayHasKey('id', $child);
                self::assertArrayHasKey('code', $child);
                self::assertArrayHasKey('title', $child);
                self::assertArrayHasKey('level', $child);
                self::assertArrayHasKey('hasChildren', $child);
                self::assertArrayHasKey('children', $child);
            }
        }

        foreach ($categories['breadcrumb'] as $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);
        }

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('alternativeTitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('colors', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            if (!in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('cashback', $product['inventory']);
                self::assertArrayHasKey('seller', $product['inventory']);
                self::assertArrayHasKey('name', $product['inventory']['seller']);
            }

            $colors = $product['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }
    }

    public function testValidationFailureOnSearchProductWhenFilterIsInvalid(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter'           => [
                        'category.id' => $this->category->getId(),
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
        self::assertEquals('Product filters is invalid!', $response['message']);
    }

    public function testValidationFailureOnSearchProductWhenSortIsInvalid(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'sort'             => [
                        '-createdAt',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
        self::assertEquals('Product sorts is invalid!', $response['message']);
    }

    public function testItThrowsExceptionOnProductSearchWhenCategoryCodeNotFound(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.sellers.products.search',
                [
                    'filter'           => [
                        'category' => 'invalid',
                    ],
                    'identifier' => $this->seller->getIdentifier()
                ]
            )
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(404, $response['status']);
        self::assertEquals('Selected category not found!', $response['detail']);
    }
}
