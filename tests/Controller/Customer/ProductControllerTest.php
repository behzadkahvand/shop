<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Entity\RateAndReview;
use App\Entity\Wishlist;
use App\Messaging\Messages\Command\Product\IncreaseProductViewsCount;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Tests\Controller\BaseControllerTestCase;

class ProductControllerTest extends BaseControllerTestCase
{
    protected ?Brand $brand;

    protected ?Category $category;

    protected ?Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $brandRepo = $this->client->getContainer()->get(BrandRepository::class);
        $this->brand = $brandRepo->findOneBy([]);

        $categoryRepo = $this->manager->getRepository(Category::class);

        $categoryRepo->rebuildClosure();
        $categoryRepo->updateLevelValues();

        $this->category = $categoryRepo->findOneBy([]);

        $this->product = $this->getService(ProductRepository::class)->findOneBy([]);
    }

    protected function tearDown(): void
    {
        unset($this->brand, $this->category);

        parent::tearDown();
    }

    public function testItCanSearchProductWithoutFiltersAndSorts()
    {
        $client = $this->sendRequest('GET', '/products/search');

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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithFilterAndSortFinalPrice()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'price' => [
                            'btn' => '10000,300000',
                        ],
                    ],
                    'sort' => [
                        '-price',
                    ],
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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithBrandIdFilterAndNewestProductSort()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'brandId' => [
                            'in' => $this->brand->getId()
                        ],
                    ],
                    'sort' => [
                        '-latest',
                    ],
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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithProductTitleFilterAndProductVisitsSort()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'title' => [
                            'like' => '%Ltd%',
                        ],
                    ],
                    'sort' => [
                        '-mostVisited',
                    ],
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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithBrandCodeAndAvailableProductFilterAndBestSellersProductSort()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'brand' => $this->brand->getCode(),
                        'available' => true,
                    ],
                    'sort' => [
                        '-bestSellers',
                    ],
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

        $brands = $metas['brands'];
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertNotNull($brands['description']);
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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithCategoryCodeAndProductOriginalityFilterAndPromotionSort()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'category' => $this->category->getCode(),
                        'original' => true,
                    ],
                    'sort' => [
                        'promotion',
                    ],
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

    public function testItCanSearchProductWithFastestDeliverySortAndWithoutFilter()
    {
        $client = $this->sendRequest(
            'GET',
            $this->route(
                'customer.products.search',
                [
                    'sort' => [
                        '-deliveryTime',
                    ],
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

            if (! in_array($product['status'], [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE])) {
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

    public function testItCanSearchProductWithPromotionFilterAndWithoutSort()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'promotion' => true,
                    ],
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

    public function testItCanSearchProductWithCampaignFilter(): void
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'is_campaign' => true,
                    ],
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
                self::assertTrue($product['inventory']['hasCampaign']);
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

    public function testValidationFailureOnSearchProductWhenFilterIsInvalid()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'category.id' => $this->category->getId(),
                    ],
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

    public function testValidationFailureOnSearchProductWhenSortIsInvalid()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'sort' => [
                        '-createdAt',
                    ],
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

    public function testItThrowsExceptionOnProductSearchWhenCategoryCodeNotFound()
    {
        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.products.search',
                [
                    'filter' => [
                        'category' => 'invalid',
                    ],
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

    public function testNotFoundForProductWithTrashedStatus(): void
    {
        $this->product->setStatus(ProductStatusDictionary::TRASHED);

        $this->manager->flush();
        $this->manager->clear();

        $client = $this->sendRequest('GET', "/products/tpi-{$this->product->getId()}");

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testItCanShowProduct()
    {
        $transport = $this->client->getContainer()->get('messenger.transport.product_views');
        $transport->reset();

        $product = $this->getService(ProductRepository::class)->findOneBy([]);
        $product->getCategory()->setCommission(500);
        $this->manager->flush();

        $client = $this->loginAs($this->customer)->sendRequest('GET', "/products/tpi-{$product->getId()}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $sentMessages = $transport->getSent();
        self::assertCount(1, $sentMessages);
        self::assertInstanceOf(
            IncreaseProductViewsCount::class,
            $sentMessages[0]->getMessage()
        );

        self::assertArrayHasKey('breadcrumb', $response['metas']);
        self::assertIsArray($response['metas']['breadcrumb']);
        foreach ($response['metas']['breadcrumb'] as $item) {
            self::assertArrayHasKey('id', $item);
            self::assertArrayHasKey('title', $item);
            self::assertArrayHasKey('code', $item);
            self::assertArrayHasKey('subtitle', $item);
            self::assertArrayHasKey('image', $item);
            self::assertArrayHasKey('pageTitle', $item);

            if ($item['image'] ?? false) {
                self::assertArrayHasKey('path', $item['image']);
                self::assertArrayHasKey('alt', $item['image']);
            }
        }

        self::assertArrayHasKey('similars', $response['metas']);
        self::assertIsArray($response['metas']['similars']);
        $statuses = [ProductStatusDictionary::SOON, ProductStatusDictionary::UNAVAILABLE];
        foreach ($response['metas']['similars'] as $item) {
            self::assertArrayHasKey('id', $item);
            self::assertArrayHasKey('title', $item);
            self::assertArrayHasKey('subtitle', $item);
            self::assertArrayHasKey('status', $item);
            self::assertArrayHasKey('featuredImage', $item);
            self::assertArrayHasKey('inventory', $item);
            self::assertArrayHasKey('colors', $item);

            self::assertArrayHasKey('path', $item['featuredImage']);
            self::assertArrayHasKey('alt', $item['featuredImage']);

            if (!in_array($item['status'], $statuses, true)) {
                self::assertArrayHasKey('id', $item['inventory']);
                self::assertArrayHasKey('price', $item['inventory']);
                self::assertArrayHasKey('finalPrice', $item['inventory']);
                self::assertArrayHasKey('suppliesIn', $item['inventory']);
                self::assertArrayHasKey('cashback', $item['inventory']);
                self::assertArrayHasKey('seller', $item['inventory']);
                self::assertArrayHasKey('hasCampaign', $item['inventory']);
                self::assertArrayHasKey('name', $item['inventory']['seller']);
            }

            $colors = $item['colors'];

            foreach ($colors as $color) {
                self::assertArrayHasKey('value', $color);
                self::assertArrayHasKey('code', $color);
                self::assertArrayHasKey('attributes', $color);
                self::assertArrayHasKey('hex', $color['attributes']);
            }
        }

        self::assertTrue($response['succeed']);

        $product = $response['results'];
        self::assertArrayHasKey('id', $product);
        self::assertArrayHasKey('title', $product);
        self::assertArrayHasKey('subtitle', $product);
        self::assertArrayHasKey('description', $product);
        self::assertArrayHasKey('EAV', $product);
        self::assertArrayHasKey('summaryEAV', $product);
        self::assertArrayHasKey('isActive', $product);
        self::assertArrayHasKey('length', $product);
        self::assertArrayHasKey('width', $product);
        self::assertArrayHasKey('height', $product);
        self::assertArrayHasKey('weight', $product);
        self::assertArrayHasKey('brand', $product);
        self::assertArrayHasKey('metaDescription', $product);
        self::assertArrayHasKey('shippingCategory', $product);
        self::assertArrayHasKey('status', $product);
        self::assertArrayHasKey('productVariants', $product);
        self::assertArrayHasKey('images', $product);
        self::assertArrayHasKey('featuredImage', $product);
        self::assertArrayHasKey('options', $product);
        self::assertArrayHasKey('category', $product);
        self::assertArrayHasKey('isOriginal', $product);
        self::assertArrayHasKey('primaryOption', $product);
        self::assertArrayHasKey('buyBoxes', $product);

        self::assertIsArray($product['buyBoxes']);
        self::assertNotEmpty($product['buyBoxes']);
        self::assertInstanceOf(\stdClass::class, json_decode(json_encode($product['buyBoxes'])));

        self::assertIsArray($product['brand']);
        foreach (['id', 'code', 'title', 'subtitle', 'metaDescription'] as $brandKey) {
            self::assertArrayHasKey($brandKey, $product['brand']);
        }

        self::assertIsArray($product['shippingCategory']);
        foreach (['id', 'name'] as $shippingCategoryKey) {
            self::assertArrayHasKey($shippingCategoryKey, $product['shippingCategory']);
        }

        self::assertIsArray($product['productVariants']);
        foreach ($product['productVariants'] as $productVariant) {
            foreach (['id', 'code', 'inventories', 'optionValues'] as $variantKey) {
                self::assertArrayHasKey($variantKey, $productVariant);
            }

            self::assertIsArray($productVariant['inventories']);
            foreach ($productVariant['inventories'] as $inventory) {
                foreach (['id', 'price', 'finalPrice', 'cashback', 'seller'] as $inventoryKey) {
                    self::assertArrayHasKey($inventoryKey, $inventory);
                }

                self::assertArrayHasKey('id', $inventory['seller']);
                self::assertArrayHasKey('name', $inventory['seller']);
                self::assertArrayHasKey('identifier', $inventory['seller']);
            }

            self::assertIsArray($productVariant['optionValues']);
            foreach ($productVariant['optionValues'] as $optionValue) {
                foreach (['id', 'value', 'code'] as $optionValueKey) {
                    self::assertArrayHasKey($optionValueKey, $optionValue);
                }
            }
        }

        self::assertIsArray($product['options']);
        foreach ($product['options'] as $option) {
            foreach (['id', 'code', 'name'] as $optionKey) {
                self::assertArrayHasKey($optionKey, $option);
            }
        }

        self::assertIsArray($product['primaryOption']);
        foreach (['id', 'code', 'name'] as $primaryOptionKey) {
            self::assertArrayHasKey($primaryOptionKey, $product['primaryOption']);
        }

        self::assertIsArray($product['buyBoxes']);
        foreach ($product['buyBoxes'] as $key => $value) {
            self::assertIsNumeric($key);
            foreach (['id', 'price', 'finalPrice'] as $buyBoxKey) {
                self::assertArrayHasKey($buyBoxKey, $value);
            }
        }
    }

    public function testItCanShowProductMetasSuccessfully(): void
    {
        $product = $this->getService(ProductRepository::class)->findOneBy([]);
        $product->getCategory()->setCommission(500);
        $this->manager->flush();

        $client = $this->loginAs($this->customer)->sendRequest('GET', "/products/tpi-{$product->getId()}/metas");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);

        $result = $response['results'];
        self::assertIsArray($result);

        self::assertArrayHasKey('wishlisted', $result);
        self::assertIsBool($result['wishlisted']);
        self::assertEquals($this->customer->getWishlist()->exists(
            fn (int $index, Wishlist $wishlist) => $wishlist->getProduct()->getId() === $product->getId()
        ), $result['wishlisted']);

        self::assertArrayHasKey('hasReview', $result);
        self::assertIsBool($result['hasReview']);
        self::assertEquals($this->customer->getRateAndReviews()->exists(
            fn (int $index, RateAndReview $rateAndReview) => $rateAndReview->getProduct()->getId() === $product->getId()
        ), $result['hasReview']);

        self::assertArrayHasKey('hasNotify', $result);
        self::assertIsBool($result['hasNotify']);
        self::assertEquals($this->customer->getProductNotifyRequests()->exists(
            fn (int $index, ProductNotifyRequest $notifyRequest) => $notifyRequest->getProduct()->getId() === $product->getId()
        ), $result['hasNotify']);
    }

    public function testItCanListAllAcceptedProductRateAndReviews(): void
    {
        $this->loginAs($this->customer)
             ->sendRequest('GET', "/products/tpi-{$this->product->getId()}/reviews");

        self::assertResponseStatusCodeSame(200);

        $response = $this->getControllerResponse();

        $rateAndReviews = $response['results'];
        self::assertIsArray($rateAndReviews);
        self::assertNotEmpty($rateAndReviews);

        foreach ($rateAndReviews as $rateAndReview) {
            self::assertArrayHasKeys([
                'id',
                'title',
                'body',
                'suggestion',
                'rate',
                'createdAt',
                'updatedAt',
                'seller',
                'customerName',
                'isBuyer',
                'productVariant',
            ], $rateAndReview);

            self::assertNotEmpty($rateAndReview['body']);
            self::assertNotEmpty($rateAndReview['suggestion']);
            self::assertNotEmpty($rateAndReview['rate']);
            self::assertGreaterThanOrEqual(1, $rateAndReview['rate']);
            self::assertLessThanOrEqual(5, $rateAndReview['rate']);

            $variant = $rateAndReview['productVariant'];
            self::assertNotEmpty($variant);

            $options = $variant['options'];
            self::assertNotEmpty($options);
            self::assertArrayHasKeys(['color', 'guarantee', 'otherOptions'], $options);

            foreach (['color', 'guarantee', 'otherOptions'] as $item) {
                self::assertNotEmpty($options[$item]);
            }

            self::assertNotEmpty($rateAndReview['customerName']);
            self::assertNotEmpty($rateAndReview['seller']);
            self::assertArrayHasKeys(['identifier', 'name'], $rateAndReview['seller']);
            self::assertNotEmpty($rateAndReview['isBuyer']);
            self::assertIsBool($rateAndReview['isBuyer']);
        }

        self::assertArrayHasKey('stats', $response['metas']);
        self::assertArrayHasKeys([
            'average_of_rate',
            'number_of_users',
            'rates_per_value',
        ], $response['metas']['stats']);
    }

    public function testItFailsOnAddingReviewIfUserProfileIsIncomplete(): void
    {
        $this->customer->setNationalNumber(null);
        $this->manager->flush();

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/products/tpi-{$this->product->getId()}/reviews",
            [
                'title' => 'My first comment title',
                'body' => 'My first comment body',
                'suggestion' => 'NO',
                'rate' => 3,
                'anonymous' => false,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Your profile is incomplete', $response['message']);
    }

    public function testItFailsOnAddingReviewIfReviewBodyIsNullOrMissing(): void
    {
        $product = $this->getService(ProductRepository::class)->findOneBy(['title' => 'product_3']);

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/products/tpi-{$product->getId()}/reviews",
            [
                'title' => 'My first comment title',
                'body' => null,
                'suggestion' => 'NO',
                'rate' => 3,
                'anonymous' => false,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Validation error has been detected!', $response['message']);
        self::assertEquals('This value should not be null.', $response['results']['body'][0]);
        self::assertEquals('This value should not be blank.', $response['results']['body'][1]);
    }

    public function testItFailsOnAddingReviewIfUserAlreadyHasReviewOnProduct(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/products/tpi-{$this->product->getId()}/reviews",
            [
                'title' => 'My first comment title',
                'body' => 'My first comment body',
                'suggestion' => 'NO',
                'rate' => 3,
                'anonymous' => false,
            ]
        );

        self::assertEquals(409, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('You already have a review on this product', $response['message']);
    }

    public function testItCanAddUserReviewOnProductSuccessfully(): void
    {
        $product = $this->getService(ProductRepository::class)->findOneBy(['title' => 'product_3']);

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/products/tpi-{$product->getId()}/reviews",
            [
                'title' => 'My first comment title',
                'body' => 'My first comment body',
                'suggestion' => 'NO',
                'rate' => 3,
                'anonymous' => false,
            ]
        );

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Your review has been added successfully', $response['message']);
    }
}
