<?php

namespace App\Tests\Controller\Seller;

use App\Dictionary\DefaultProductOptionCode;
use App\Dictionary\ProductStatusDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\CategoryBrandSellerProductOption;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductOptionRepository;
use App\Tests\Controller\BaseControllerTestCase;

class ProductControllerTest extends BaseControllerTestCase
{
    protected ?Brand $brand;

    protected ?Category $category;

    protected ?Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $brandRepo   = $this->client->getContainer()->get(BrandRepository::class);
        $this->brand = $brandRepo->findOneBy([]);

        $categoryRepo = $this->manager->getRepository(Category::class);

        $categoryRepo->rebuildClosure();
        $categoryRepo->updateLevelValues();

        $this->category = $categoryRepo->findOneBy(['code' => 'category_room']);

        $this->product = $this->manager->getRepository(Product::class)->findOneBy([]);
        $this->product->setSeller($this->seller);
        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        unset($this->brand, $this->category, $this->product);

        parent::tearDown();
    }

    public function testIndex(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.products.index')
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $results = $response['results'];
        self::assertNotEmpty($results);

        foreach ($results as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('isOriginal', $product);
            self::assertArrayHasKey("status", $product);
            self::assertArrayHasKey('brand', $product);
            self::assertArrayHasKey('title', $product['brand']);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('title', $product['category']);
            self::assertArrayHasKey('inventoryCount', $product);
            self::assertArrayHasKey('seller', $product);
            if (null !== $product['seller']) {
                self::assertArrayHasKey('identifier', $product['seller']);
                self::assertArrayHasKey('name', $product['seller']);
            }
            self::assertArrayHasKey('isSeller', $product);
            self::assertTrue($product['isSeller']);
        }
    }

    public function testIndexSellerProducts(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest('GET', '/seller/products/me');

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $results = $response['results'];
        self::assertIsArray($results);
        self::assertNotEmpty($results);

        foreach ($results as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('isOriginal', $product);
            self::assertArrayHasKey("status", $product);
            self::assertArrayHasKey('brand', $product);
            self::assertArrayHasKey('title', $product['brand']);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('title', $product['category']);
            self::assertArrayHasKey('inventoryCount', $product);
            self::assertArrayHasKey('seller', $product);
            if (null !== $product['seller']) {
                self::assertArrayHasKey('identifier', $product['seller']);
                self::assertArrayHasKey('name', $product['seller']);
            }
            self::assertArrayHasKey('isSeller', $product);
            self::assertTrue($product['isSeller']);
        }
    }

    public function testIndexFailWithInvalidStatus(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.products.index'),
            [],
            [
                'filter' => [
                    'status' => 'test',
                ],
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testNotFoundForProductWithTrashedStatus(): void
    {
        $this->product->setStatus(ProductStatusDictionary::TRASHED);

        $this->manager->flush();
        $this->manager->clear();

        $url = $this->route('seller.products.productVariant.index', ['id' => $this->product->getId()]);

        $client = $this->loginAs($this->seller)->sendRequest('GET', $url);

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testProductVariantIndex(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.products.productVariant.index', ['id' => $this->product->getId()])
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $results = $response['results'];

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('title', $results);
        self::assertArrayHasKey('isOriginal', $results);
        self::assertArrayHasKey('featuredImage', $results);
        self::assertArrayHasKey('category', $results);
        self::assertArrayHasKey('productVariants', $results);
        self::assertArrayHasKey('seller', $results);
        self::assertArrayHasKey('referencePrice', $results);
        self::assertArrayHasKey('priceTopMargin', $results);
        self::assertArrayHasKey('priceBottomMargin', $results);

        if (null !== $results['seller']) {
            self::assertArrayHasKey('identifier', $results['seller']);
            self::assertArrayHasKey('name', $results['seller']);
        }

        self::assertArrayHasKey('path', $results['featuredImage']);

        $category = $results['category'];

        self::assertArrayHasKey('title', $category);
        self::assertArrayHasKey('commission', $category);

        $productVariants = $results['productVariants'];

        foreach ($productVariants as $productVariant) {
            self::assertArrayHasKey('id', $productVariant);
            self::assertArrayHasKey('inventories', $productVariant);
            self::assertArrayHasKey('optionValues', $productVariant);
            self::assertArrayHasKey('title', $productVariant);
            self::assertArrayHasKey('maxLead', $productVariant);

            $inventories = $productVariant['inventories'];

            self::assertCount(1, $inventories);

            foreach ($inventories as $inventory) {
                self::assertArrayHasKey('id', $inventory);
                self::assertArrayHasKey('stock', $inventory);
                self::assertArrayHasKey('price', $inventory);
                self::assertArrayHasKey('finalPrice', $inventory);
                self::assertArrayHasKey('isActive', $inventory);
                self::assertArrayHasKey('maxPurchasePerOrder', $inventory);
                self::assertArrayHasKey('suppliesIn', $inventory);
                self::assertArrayHasKey('sellerCode', $inventory);
            }

            $optionValues = $productVariant['optionValues'];

            foreach ($optionValues as $optionValue) {
                self::assertArrayHasKey('value', $optionValue);
            }
        }
    }

    public function testProductVariantOptions(): void
    {
        /** @var ProductOption $productOption */
        $productOption = $this->getService(ProductOptionRepository::class)
                              ->findOneBy(['code' => DefaultProductOptionCode::GUARANTEE]);

        $categoryBrandSellerProductOption = new CategoryBrandSellerProductOption();
        $categoryBrandSellerProductOption->setCategory($this->product->getCategory())
                                         ->setBrand($this->product->getBrand())
                                         ->setProductOption($productOption);

        $optionValues = $productOption->getValues()->slice(0, 2);

        foreach ($optionValues as $value) {
            $categoryBrandSellerProductOption->addValue($value);
        }

        $this->manager->persist($categoryBrandSellerProductOption);
        $this->manager->flush();
        $this->manager->clear();

        $this->loginAs($this->seller)
             ->sendRequest('GET', $this->route(
                 'seller.products.productVariant.options.index',
                 ['id' => $this->product->getId()]
             ));

        self::assertResponseIsSuccessful();

        $response                   = $this->getControllerResponse();
        $allowedProductOptionValues = array_map(static fn(ProductOptionValue $pov) => $pov->getId(), $optionValues);

        foreach ($response['results'] as $_productOption) {
            self::assertArrayHasKey('id', $_productOption);
            self::assertArrayHasKey('name', $_productOption);
            self::assertArrayHasKey('code', $_productOption);
            self::assertArrayHasKey('values', $_productOption);
            self::assertIsArray($_productOption['values']);

            foreach ($_productOption['values'] as $value) {
                self::assertArrayHasKey('id', $value);
                self::assertArrayHasKey('code', $value);
                self::assertArrayHasKey('value', $value);
                self::assertArrayHasKey('attributes', $value);
            }

            if ($_productOption['code'] === DefaultProductOptionCode::GUARANTEE) {
                $_productOptionValues = array_column($_productOption['values'], 'id');

                self::assertNotEmpty(array_intersect($allowedProductOptionValues, $_productOptionValues));
            }
        }
    }

    public function testProductVariantIndexFailWithNotFound(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.products.productVariant.index', ['id' => 0])
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(404, $response['status']);
        self::assertEquals('Selected product not found!', $response['detail']);
    }

    public function testItCanSearchProductWithoutFiltersAndSorts(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest('GET', $this->router->generate('seller.products.search'));

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
        self::assertNull($brands['title']);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            self::assertFalse($category['hasChildren']);
            self::assertEquals([], $category['children']);
        }

        self::assertEquals([], $categories['breadcrumb']);

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('productPrices', $product);
            self::assertArrayHasKey('isSeller', $product);
            self::assertArrayHasKey('isOriginal', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            self::assertArrayHasKey('title', $product['category']);

            if (!empty($product['productPrices'])) {
                self::assertArrayHasKey('price', $product['productPrices']);
                self::assertArrayHasKey('finalPrice', $product['productPrices']);
                self::assertNotEquals(0, $product['productPrices']['price']);
                self::assertNotEquals(0, $product['productPrices']['finalPrice']);
            } else {
                self::assertEquals(null, $product['productPrices']);
            }
        }
    }

    public function testItCanSearchProductWithBrandIdFilterAndWithoutSorts(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'brandId' => $this->brand->getId(),
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
        self::assertNull($brands['title']);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            self::assertFalse($category['hasChildren']);
            self::assertEquals([], $category['children']);
        }

        self::assertEquals([], $categories['breadcrumb']);

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('productPrices', $product);
            self::assertArrayHasKey('isSeller', $product);
            self::assertArrayHasKey('isOriginal', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            self::assertArrayHasKey('title', $product['category']);

            if (is_array($product['productPrices'])) {
                self::assertArrayHasKey('price', $product['productPrices']);
                self::assertArrayHasKey('finalPrice', $product['productPrices']);
                self::assertNotEquals(0, $product['productPrices']['price']);
                self::assertNotEquals(0, $product['productPrices']['finalPrice']);
            } else {
                self::assertNull($product['productPrices']);
            }
        }
    }

    public function testItCanSearchProductWithProductTitleFilterAndWithoutSorts(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'title' => [
                                           'like' => 'Ltd',
                                       ],
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
        self::assertNull($brands['title']);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            self::assertFalse($category['hasChildren']);
            self::assertEquals([], $category['children']);
        }

        self::assertEquals([], $categories['breadcrumb']);

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('productPrices', $product);
            self::assertArrayHasKey('isSeller', $product);
            self::assertArrayHasKey('isOriginal', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            self::assertArrayHasKey('title', $product['category']);

            if (is_array($product['productPrices'])) {
                self::assertArrayHasKey('price', $product['productPrices']);
                self::assertArrayHasKey('finalPrice', $product['productPrices']);
                self::assertNotEquals(0, $product['productPrices']['price']);
                self::assertNotEquals(0, $product['productPrices']['finalPrice']);
            } else {
                self::assertNull($product['productPrices']);
            }
        }
    }

    public function testItCanSearchProductWithProductIdFilterAndWithoutSorts(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'title' => [
                                           'like' => 'tpi-' . $this->product->getId(),
                                       ],
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

        self::assertCount(1, $response['results']);
        foreach ($response['results'] as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertEquals($this->product->getId(), $product['id']);
        }
    }

    public function testItCanSearchProductWithProductStatusFilterAndWithoutSorts(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'status' => ProductStatusDictionary::CONFIRMED,
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
        self::assertNull($brands['title']);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
        self::assertArrayHasKey('breadcrumb', $categories);
        self::assertArrayHasKey('hierarchy', $categories);

        foreach ($categories['hierarchy'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('code', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('level', $category);
            self::assertArrayHasKey('hasChildren', $category);
            self::assertArrayHasKey('children', $category);

            self::assertFalse($category['hasChildren']);
            self::assertEquals([], $category['children']);
        }

        self::assertEquals([], $categories['breadcrumb']);

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('productPrices', $product);
            self::assertArrayHasKey('isSeller', $product);
            self::assertArrayHasKey('isOriginal', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            self::assertArrayHasKey('title', $product['category']);

            self::assertArrayHasKey('price', $product['productPrices']);
            self::assertArrayHasKey('finalPrice', $product['productPrices']);
            self::assertNotEquals(0, $product['productPrices']['price']);
            self::assertNotEquals(0, $product['productPrices']['finalPrice']);
        }
    }

    public function testItCanSearchProductWithCategoryCodeAndProductOriginalityFilterAndWithoutSort(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->router->generate(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'category' => $this->category->getCode(),
                                       'original' => true,
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
        self::assertNull($brands['title']);
        self::assertArrayHasKey('items', $brands);

        foreach ($brands['items'] as $brand) {
            self::assertArrayHasKey('id', $brand);
            self::assertArrayHasKey('title', $brand);
            self::assertArrayHasKey('code', $brand);
        }

        $categories = $metas['categories'];
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

            if (!$category['hasChildren']) {
                self::assertEquals([], $category['children']);
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
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('productPrices', $product);
            self::assertArrayHasKey('isSeller', $product);
            self::assertArrayHasKey('isOriginal', $product);

            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);

            self::assertArrayHasKey('title', $product['category']);

            if (!empty($product['productPrices'])) {
                self::assertArrayHasKey('price', $product['productPrices']);
                self::assertArrayHasKey('finalPrice', $product['productPrices']);
                self::assertNotEquals(0, $product['productPrices']['price']);
                self::assertNotEquals(0, $product['productPrices']['finalPrice']);
            } else {
                self::assertEquals(null, $product['productPrices']);
            }
        }
    }

    public function testValidationFailureOnSearchProductWhenStatusFilterIsInvalid(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
                               [
                                   'filter' => [
                                       'status' => ProductStatusDictionary::DRAFT,
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
        self::assertEquals('Product status filter is invalid!', $response['message']);
    }

    public function testValidationFailureOnSearchProductWhenFilterIsInvalid(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
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

    public function testValidationFailureOnSearchProductWhenSortIsNotEmpty(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           $this->route(
                               'seller.products.search',
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

    public function testIndexRootCategories(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest('GET', '/seller/products/categories');

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        foreach ($response['results'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('leaf', $category);
            self::assertFalse($category['leaf']);
        }
    }

    public function testIndexChildCategories(): void
    {
        $rootCategory = $this->getService(CategoryRepository::class)
                             ->findOneBy(['parent' => null]);

        $client = $this->loginAs($this->seller)
                       ->sendRequest('GET', "/seller/products/categories?parent={$rootCategory->getId()}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        foreach ($response['results'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('leaf', $category);
        }
    }

    public function testIndexBrands(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest('GET', '/seller/products/brands');

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        foreach ($response['results'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('title', $category);
        }
    }

    public function testIndexForBrandWithTitle(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest('GET', "/seller/products/brands?title={$this->brand->getTitle()}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        foreach ($response['results'] as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('title', $category);
        }
    }

    public function testStore(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest('POST', '/seller/products', [
                           'title'         => 'seller product title',
                           'subtitle'      => 'seller product subtitle',
                           'description'   => 'seller product description',
                           'link'          => 'https://timcheh.com/',
                           'weight'        => 100,
                           'width'         => 100,
                           'length'        => 100,
                           'height'        => 100,
                           'brand'         => $this->brand->getId(),
                           'category'      => $this->category->getId(),
                           'featuredImage' => ['path' => '/seller/products/image.jpg'],
                           'images'        => [['path' => '/seller/products/image.jpg']],
                       ]);

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $product = $response['results'];
        self::assertArrayHasKey('id', $product);
        self::assertArrayHasKey('title', $product);
        self::assertArrayHasKey('isOriginal', $product);
        self::assertArrayHasKey('status', $product);

        self::assertArrayHasKey('images', $product);
        self::assertIsArray($product['images']);
        foreach ($product['images'] as $image) {
            self::assertArrayHasKey('alt', $image);
            self::assertArrayHasKey('path', $image);
        }

        self::assertArrayHasKey('featuredImage', $product);
        self::assertArrayHasKey('alt', $product['featuredImage']);
        self::assertArrayHasKey('path', $product['featuredImage']);

        self::assertArrayHasKey('category', $product);
        self::assertArrayHasKey('title', $product['category']);

        self::assertArrayHasKey('productPrices', $product);
        self::assertNull($product['productPrices']);

        self::assertArrayHasKey('inventoryCount', $product);
        self::assertEquals(0, $product['inventoryCount']);

        self::assertArrayHasKey('isSeller', $product);
        self::assertFalse($product['isSeller']);
    }

    public function testItCanGetBestSellerProducts(): void
    {
        $this->loginAs($this->seller)
             ->getJson($this->route('seller.products.best_seller'));

        $this->assertResponseIsSuccessful();

        $response = $this->getControllerResponse();

        self::assertArrayHasKeys(['succeed', 'message', 'results', 'metas'], $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $products = $response['results'];

        foreach ($products as $product) {
            self::assertArrayHasKeys(['id', 'title', 'itemCount', 'featuredImagePath', 'featuredImageAlt'], $product);
        }
    }
}
