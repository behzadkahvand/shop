<?php

namespace App\Tests\Controller\Customer;

use App\Entity\Product;
use App\Tests\Controller\BaseControllerTestCase;
use Doctrine\Persistence\ObjectRepository;

final class ProductAttributeControllerTest extends BaseControllerTestCase
{
    private ObjectRepository $productRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->manager->getRepository(Product::class);
    }

    protected function tearDown(): void
    {
        unset($this->productRepository);

        parent::tearDown();
    }

    public function testShow()
    {
        $product = $this->productRepository->findOneBy([]);
        $client  = $this->sendRequest('GET', $this->route('customer.product.attribute.show', ['id' => $product->getId()]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
