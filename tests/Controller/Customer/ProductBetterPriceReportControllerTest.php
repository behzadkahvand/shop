<?php

namespace App\Tests\Controller\Customer;

use App\Entity\ProductBetterPriceReport;
use App\Entity\Product;
use App\Tests\Controller\BaseControllerTestCase;

class ProductBetterPriceReportControllerTest extends BaseControllerTestCase
{
    private ?Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->manager->getRepository(Product::class)->findOneBy([]);
    }

    protected function tearDown(): void
    {
        unset($this->product);

        parent::tearDown();
    }

    public function testStore()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.product.better.price.store'),
            [
                'price'   => 1234,
                'product' => $this->product->getId(),
                'website' => 'http://test@test.com',
            ]
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testStoreFailForLimitation()
    {
        $report1 = new ProductBetterPriceReport();
        $report1->setCustomer($this->customer)
                ->setPrice(2000)
                ->setProduct($this->product)
                ->setWebsite('https://test.com');

        $report2 = new ProductBetterPriceReport();
        $report2->setCustomer($this->customer)
                ->setPrice(3000)
                ->setProduct($this->product)
                ->setWebsite('https://foo.com');
        $this->manager->persist($report1);
        $this->manager->persist($report2);
        $this->manager->flush();

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.product.better.price.store'),
            [
                'price'   => 1234,
                'product' => $this->product->getId(),
                'website' => 'http://test@test.com',
            ]
        );

        $this->assertEquals(429, $client->getResponse()->getStatusCode());
    }

    public function testStoreValidationErrorForWebsite()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.product.better.price.store'),
            [
                'price'   => 1234,
                'product' => $this->product->getId(),
                'website' => 'test',
            ]
        );

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testStoreValidationErrorForStoreName()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.product.better.price.store'),
            [
                'price'   => 1234,
                'product' => $this->product->getId(),
            ]
        );

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
