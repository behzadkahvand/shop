<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Wishlist;
use App\Repository\InventoryRepository;
use App\Repository\ProductRepository;
use App\Tests\Controller\BaseControllerTestCase;

class WishlistControllerTest extends BaseControllerTestCase
{
    protected ProductRepository $productRepo;

    protected InventoryRepository $inventoryRepo;

    private ?Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepo = $this->manager->getRepository(Product::class);

        $this->inventoryRepo = $this->manager->getRepository(Inventory::class);

        // @inventory_20 in fixtures
        $activeInventory = $this->inventoryRepo->findOneBy([
            'isActive' => true,
            'price' => 20,
            'finalPrice' => 20,
        ]);

        $this->product = $activeInventory->getVariant()->getProduct();
    }

    public function testItCanAddProductToWishlistSuccessfully(): void
    {
        $this->product->setStatus(ProductStatusDictionary::CONFIRMED);

        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $this->product->getId()])
            );

        self::assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testItCanStoreIfInventoryIsNull(): void
    {
        $variant = new ProductVariant();
        $this->product->setStatus(ProductStatusDictionary::CONFIRMED);
        $this->product->addProductVariant($variant);
        $this->manager->persist($variant);
        $this->manager->flush();

        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $this->product->getId()])
            );

        self::assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testItCanNotAddUnconfirmedProductToWishlist(): void
    {
        $product = $this->productRepo->findOneBy([
            'status' => ProductStatusDictionary::DRAFT,
        ]);

        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $product->getId()])
            );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('product', $results);
        self::assertEquals('The selected product must have a allowed status!', $results['product'][0]);
    }

    public function testItCanNotAddProductToWishlistWhenStatusIsNotAllowed(): void
    {
        $this->product->setStatus(ProductStatusDictionary::REJECTED);

        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $this->product->getId()])
            );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('product', $results);
        self::assertEquals('The selected product must have a allowed status!', $results['product'][0]);
    }

    public function testItFailsOnStoreWhenStatusIsNotAllowed(): void
    {
        $this->product->setStatus(ProductStatusDictionary::CONFIRMED);

        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $this->product->getId()])
            );

        self::assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testAddDuplicate(): void
    {
        $wishlist = $this->createMockWishlist();
        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'POST',
                $this->route('customer.wishlists.store', ['id' => $wishlist->getProduct()->getId()])
            );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testIndex(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest('GET', $this->route('customer.wishlists.index'));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDestroy(): void
    {
        $wishlist = $this->createMockWishlist();
        $client = $this->loginAs($this->customer)
            ->sendRequest(
                'DELETE',
                $this->route('customer.wishlists.destroy', ['id' => $wishlist->getProduct()->getId()])
            );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    private function createMockWishlist(): Wishlist
    {
        $wishlist = (new Wishlist())->setCustomer($this->customer)->setProduct($this->product);

        $this->manager->persist($wishlist);
        $this->manager->flush();

        return $wishlist;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->product, $this->productRepo, $this->inventoryRepo);
    }
}
