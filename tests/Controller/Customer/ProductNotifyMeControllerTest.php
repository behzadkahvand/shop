<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Repository\ProductRepository;
use App\Tests\Controller\BaseControllerTestCase;

class ProductNotifyMeControllerTest extends BaseControllerTestCase
{
    private ProductRepository $productRepo;

    private ?Product $product;

    public function testCustomerCanAddProductToNotifyMeRSuccessfully(): void
    {
        $client = $this->loginAs($this->customer)
                       ->postJson(
                           $this->route('customer.notify-me.store', ['id' => $this->product->getId()])
                       );

        self::assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testItFailsOnStoreWhenTheRequestIsDuplicate(): void
    {
        $this->createMockNotifyRequest();
        $client = $this->loginAs($this->customer)
                       ->postJson(
                           $this->route('customer.notify-me.store', ['id' => $this->product->getId()])
                       );

        self::assertEquals(409, $client->getResponse()->getStatusCode());
    }

    public function testItCanNotAddProductToNotifyRequestIfItIsNotUnavailable(): void
    {
        $product = $this->productRepo->findOneBy(
            [
                'status' => ProductStatusDictionary::DRAFT, // everything except UNAVAILABLE
            ]
        );

        $client = $this->loginAs($this->customer)
                       ->postJson(
                           $this->route('customer.notify-me.store', ['id' => $product->getId()])
                       );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertFalse($response['succeed']);
        $results = $response['results'];

        self::assertEquals('The selected product must have a allowed status!', $results['product'][0]);
    }

    public function testIndex(): void
    {
        $client = $this->loginAs($this->customer)
                       ->getJson($this->route('customer.notify-me.index'));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDestroy(): void
    {
        $notifyReq = $this->createMockNotifyRequest();
        $client    = $this->loginAs($this->customer)
                          ->deleteJson(
                              $this->route('customer.notify-me.destroy', ['id' => $notifyReq->getProduct()->getId()])
                          );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testItFailsOnDestroyWhenNotExistsNotifyRequest(): void
    {
        $client = $this->loginAs($this->customer)
                       ->deleteJson(
                           $this->route('customer.notify-me.destroy', ['id' => $this->product->getId()])
                       );

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepo = $this->manager->getRepository(Product::class);

        $this->product = $this->productRepo->findOneBy(['title' => 'product_12']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->product, $this->productRepo);
    }

    private function createMockNotifyRequest(): ProductNotifyRequest
    {
        $notifyRequest = (new ProductNotifyRequest())->setCustomer($this->customer)->setProduct($this->product);

        $this->manager->persist($notifyRequest);
        $this->manager->flush();

        return $notifyRequest;
    }
}
