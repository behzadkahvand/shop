<?php

namespace App\Tests\Controller\Seller;

use App\Tests\Controller\BaseControllerTestCase;

class CategoryControllerTest extends BaseControllerTestCase
{
    public function testItCanGetSellerCategoriesList()
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.categories.index.seller'),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $categories = $response['results'];

        foreach ($categories as $category) {
            self::assertArrayHasKey('id', $category);
            self::assertArrayHasKey('title', $category);
            self::assertArrayHasKey('code', $category);
        }
    }

    public function testItCanSearchProductsWithTitle(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.categories.product.search', [
                'filter' => [
                    'title' => 'Ltd',
                ],
            ]),
        );

        self::assertResponseIsSuccessful();

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $categories = $response['results'];
        self::assertIsArray($categories);
        self::assertNotEmpty($categories);

        foreach ($categories as $category) {
            self::assertArrayHasKeys(['id', 'code', 'title', 'breadcrumb'], $category);
            self::assertIsArray($category['breadcrumb']);
            self::assertNotEmpty($category['breadcrumb']);

            foreach ($category['breadcrumb'] as $item) {
                self::assertArrayHasKeys(['id', 'code', 'title'], $item);
            }
        }
    }
}
