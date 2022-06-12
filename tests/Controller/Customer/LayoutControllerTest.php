<?php

namespace App\Tests\Controller\Customer;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Tests\Controller\BaseControllerTestCase;

class LayoutControllerTest extends BaseControllerTestCase
{
    public function testItCanGenerateLayoutBlocks(): void
    {
        $response = $this->sendRequest('GET', '/layout/blocks');

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $results = data_get($this->getControllerResponse(), 'results');
        self::assertArrayHasKey('categories', $results);
        self::assertArrayHasKey('products', $results);
        self::assertArrayHasKey('promotions', $results);
        self::assertEmpty($results['promotions']);
        self::assertEmpty($results['products']);
        self::assertEmpty($results['categories']);
    }

    public function testItCanGenerateCategoryLayoutBlocksForCategories(): void
    {
        $categories  = $this->getService(CategoryRepository::class)->findBy(['title' => ['mobile', 'category_home']]);

        $response   = $this->sendRequest(
            'GET',
            $this->route('customer.layout.blocks', [
                'categories' => array_map(static fn(Category $category) => $category->getCode(), $categories),
            ])
        );

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
        self::assertArrayHasKey('categories', $results);
        self::assertArrayHasKey('products', $results);
        self::assertArrayHasKey('promotions', $results);
        foreach ($results['categories'] as $category => $products) {
            foreach ($products as $product) {
                self::assertArrayHasKey('id', $product);
                self::assertArrayHasKey('title', $product);
                self::assertArrayHasKey('subtitle', $product);
                self::assertArrayHasKey('status', $product);
                self::assertArrayHasKey('featuredImage', $product);
                self::assertArrayHasKey('path', $product['featuredImage']);
                self::assertArrayHasKey('alt', $product['featuredImage']);
                self::assertArrayHasKey('inventory', $product);
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('colors', $product);
            }
        }
    }

    public function testItCanGenerateCategoryLayoutBlocksForCategoriesWithPromotion(): void
    {
        $categories  = $this->getService(CategoryRepository::class)->findBy(['title' => ['mobile', 'category_home']]);

        $response   = $this->sendRequest(
            'GET',
            $this->route('customer.layout.blocks', [
                'categories' => array_map(static fn(Category $category) => $category->getCode(), $categories),
            ])
        );

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
        self::assertArrayHasKey('categories', $results);
        self::assertArrayHasKey('products', $results);
        self::assertArrayHasKey('promotions', $results);
        foreach ($results['categories'] as $category => $products) {
            foreach ($products as $product) {
                self::assertArrayHasKey('id', $product);
                self::assertArrayHasKey('title', $product);
                self::assertArrayHasKey('subtitle', $product);
                self::assertArrayHasKey('status', $product);
                self::assertArrayHasKey('featuredImage', $product);
                self::assertArrayHasKey('path', $product['featuredImage']);
                self::assertArrayHasKey('alt', $product['featuredImage']);
                self::assertArrayHasKey('inventory', $product);
                self::assertArrayHasKey('id', $product['inventory']);
                self::assertArrayHasKey('price', $product['inventory']);
                self::assertArrayHasKey('finalPrice', $product['inventory']);
                self::assertArrayHasKey('suppliesIn', $product['inventory']);
                self::assertArrayHasKey('colors', $product);
            }
        }
    }

    public function testItCanGenerateProductLayoutBlocksForDesiredProducts(): void
    {
        $products = $this->getService(ProductRepository::class)->findBy(['title' => ['product_1', 'product_2']]);

        $response = $this->sendRequest(
            'GET',
            $this->route('customer.layout.blocks', [
                'products' => array_map(static fn(Product $product) => $product->getId(), $products),
            ])
        );

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
        self::assertArrayHasKey('categories', $results);
        self::assertArrayHasKey('products', $results);
        self::assertArrayHasKey('promotions', $results);
        foreach ($results['products'] as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('id', $product['inventory']);
            self::assertArrayHasKey('price', $product['inventory']);
            self::assertArrayHasKey('finalPrice', $product['inventory']);
            self::assertArrayHasKey('suppliesIn', $product['inventory']);
            self::assertArrayHasKey('colors', $product);
        }
    }

    public function testItCanGenerateOnSaleProductLayoutBlocksAndOnSaleInventoryLayoutBlocks(): void
    {
        //@TODO Test data
        $response = $this->sendRequest(
            'GET',
            $this->route('customer.layout.on-sale-blocks')
        );

        $results = data_get($this->getControllerResponse(), 'results');
        self::assertEquals(200, $response->getResponse()->getStatusCode());
        self::assertArrayHasKey('onSaleProducts', $results);
        self::assertArrayHasKey('onSaleInventories', $results);
        self::assertCount(3, $results['onSaleProducts']);
        self::assertCount(1, $results['onSaleInventories']);
        foreach ($results['onSaleProducts'] as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('id', $product['inventory']);
            self::assertArrayHasKey('price', $product['inventory']);
            self::assertArrayHasKey('finalPrice', $product['inventory']);
            self::assertArrayHasKey('suppliesIn', $product['inventory']);
            self::assertArrayHasKey('stockInventory', $product['inventory']);
            self::assertArrayHasKey('colors', $product);
        }

        foreach ($results['onSaleInventories'] as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('subtitle', $product);
            self::assertArrayHasKey('status', $product);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('alt', $product['featuredImage']);
            self::assertArrayHasKey('inventory', $product);
            self::assertArrayHasKey('id', $product['inventory']);
            self::assertArrayHasKey('price', $product['inventory']);
            self::assertArrayHasKey('finalPrice', $product['inventory']);
            self::assertArrayHasKey('suppliesIn', $product['inventory']);
            self::assertArrayHasKey('colors', $product);
            self::assertTrue($product['inventory']['hasCampaign']);
        }
    }
}
