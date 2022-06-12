<?php

namespace App\Tests\Controller\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\CategoryDiscountRange;
use App\Entity\CategoryProductIdentifier;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Repository\SellerRepository;
use App\Service\ORM\QueryBuilderFilterService;
use App\Tests\Controller\BaseControllerTestCase;

class ProductVariantControllerTest extends BaseControllerTestCase
{
    protected SellerRepository $sellerRepo;

    protected ?ProductVariant $variant;

    protected ?Product $product;

    protected array $productOptionValues;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerRepo = $this->manager->getRepository(Seller::class);
        $this->seller = $this->sellerRepo->findOneBy(['name' => 'LENDO']);
        $inventory = $this->manager->getRepository(Inventory::class)->findOneBy(['seller' => $this->seller]);
        $this->variant = $inventory->getVariant();
        $this->product = $this->manager->getRepository(Product::class)->findOneBy([]);
        $this->variant->getProduct()->setStatus('CONFIRMED');
        $productOptionIds = $this->product->getOptions()->map(fn ($option) => $option->getId())->toArray();
        $this->productOptionValues = $this->manager->getRepository(ProductOptionValue::class)->findBy([
            'option' => $productOptionIds,
        ]);
    }

    protected function tearDown(): void
    {
        unset($this->sellerRepo, $this->seller, $this->variant, $this->product, $this->productOptionValues);

        parent::tearDown();
    }

    public function testIndex(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.product_variants.index')
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $results = $response['results'];
        self::assertIsArray($results);

        foreach ($results as $result) {
            self::assertArrayHasKey('id', $result);
            self::assertArrayHasKey('product', $result);
            self::assertArrayHasKey('inventories', $result);
            self::assertArrayHasKey('optionValues', $result);
            self::assertArrayHasKey('maxLead', $result);

            $product = $result['product'];
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('title', $product['category']);
            self::assertArrayHasKey('featuredImage', $product);
            self::assertArrayHasKey('path', $product['featuredImage']);
            self::assertArrayHasKey('referencePrice', $product);
            self::assertArrayHasKey('priceTopMargin', $product);
            self::assertArrayHasKey('priceBottomMargin', $product);

            $inventories = $result['inventories'];
            self::assertIsArray($inventories);

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

            $optionValues = $result['optionValues'];
            self::assertIsArray($optionValues);

            foreach ($optionValues as $value) {
                self::assertArrayHasKey('value', $value);
            }
        }

        $metas = $response['metas'];

        self::assertArrayHasKey('page', $metas);
        self::assertArrayHasKey('perPage', $metas);
        self::assertArrayHasKey('totalItems', $metas);
        self::assertArrayHasKey('totalPages', $metas);
    }

    public function testItDoesNotHaveAccessToUpdateInventory(): void
    {
        $context = [
            'filter' => [
                'id' => ['neq' => $this->seller->getId()],
            ],
            'sort' => ['-id']
        ];

        $seller = $this->getService(QueryBuilderFilterService::class)
            ->filter(Seller::class, $context)
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();

        $client = $this->loginAs($seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()])
        );

        self::assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testItCanUpdateInventory(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 20,
                'price' => 150000,
                'finalPrice' => 140000,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
                'sellerCode' => 'sellerCode',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertCount(8, $response['results']);
        self::assertArrayHasKey('stock', $response['results']);
        self::assertArrayHasKey('price', $response['results']);
        self::assertArrayHasKey('finalPrice', $response['results']);
        self::assertArrayHasKey('isActive', $response['results']);
        self::assertArrayHasKey('maxPurchasePerOrder', $response['results']);
        self::assertArrayHasKey('suppliesIn', $response['results']);
        self::assertArrayHasKey('status', $response['results']);
        self::assertArrayHasKey('variant', $response['results']);
        self::assertArrayHasKey('id', $response['results']['variant']);
        self::assertArrayHasKey('metas', $response);
        self::assertEquals([], $response['metas']);
        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
    }

    public function testItFailsUpdateAnInventoryWhenProductIdentifierConstraintIsNotResolved(): void
    {
        $category = $this->variant->getProduct()->getCategory();
        $categoryProductIdentifier = new CategoryProductIdentifier();
        $categoryProductIdentifier->setRequired(true);

        $category->setCategoryProductIdentifier($categoryProductIdentifier);

        $this->manager->persist($categoryProductIdentifier);
        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 20,
                'price' => 150000,
                'finalPrice' => 140000,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
            ]
        );

        self::assertResponseStatusCodeSame(422);

        $response = $this->getControllerResponse();

        self::assertArrayHasKeys(['succeed', 'message', 'results', 'metas'], $response);
        self::assertArrayHasKey('inventory', $response['results']);
        self::assertEquals('Validation error has been detected!', $response['message']);
        self::assertFalse($response['succeed']);
        self::assertContains('برای این کالا شناسه ی کالا وارد نشده است', $response['results']['inventory']);
    }

    public function testItFailsUpdateAnInventoryWhenMaxPurchasePerOrderIsNegative(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 10,
                'price' => 120_000,
                'finalPrice' => 120_000,
                'maxPurchasePerOrder' => -2,
                'suppliesIn' => 1,
                'isActive' => true,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('maxPurchasePerOrder', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals(
            'This value should be either positive or zero.',
            $response['results']['maxPurchasePerOrder'][0]
        );
    }

    public function testItFailsUpdateAnInventoryWhenDiscountRangeIsInvalid(): void
    {
        $category      = $this->variant->getProduct()->getCategory();
        $discountRange = new CategoryDiscountRange();
        $discountRange->setMinDiscount(1)->setMaxDiscount(20)->setCategory($category);
        $category->setDiscountRange($discountRange);

        $this->manager->persist($discountRange);
        $this->manager->flush();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 10,
                'price' => 12_000_000,
                'finalPrice' => 2_000_000,
                'maxPurchasePerOrder' => -2,
                'suppliesIn' => 1,
                'isActive' => true,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('maxPurchasePerOrder', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertMatchesRegularExpression(
            '/^شما نمیتوانید کمتر از \d+? درصد و بیشتر از \d+? درصد روی این کتگوری تخفیف بدهید\.$/',
            $response['results']['finalPrice'][0]
        );
    }

    public function testShouldNotUpdateInventoryWhenInventoryPriceViolatesProductReferencePriceRule(): void
    {
        /** @var Product $product */
        $product      = $this->variant->getProduct();
        $product->setReferencePrice(10_000_000);
        $product->setPriceTopMargin(10);

        $this->manager->flush();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 20,
                'price' => 12_000_000,
                'finalPrice' => 8_000_000,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
                'sellerCode' => 'sellerCode',
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        self::assertEquals('Inventory price is out of reference price margin', $response['detail']);
    }

    public function testItFailsUpdateAnInventoryWhenStockIsNegative(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => -20,
                'price' => 120_000,
                'finalPrice' => 120_000,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('stock', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals(
            'This value should be either positive or zero.',
            $response['results']['stock'][0]
        );
    }

    public function testItFailsUpdateAnInventoryWhenPriceIsLessThanFinalPrice(): void
    {
        $finalPrice = 160_000;

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 20,
                'price' => 120_000,
                'finalPrice' => $finalPrice,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('price', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals(
            sprintf('This value should be greater than or equal to %s.', $finalPrice),
            $response['results']['price'][0]
        );
    }

    public function testItCanCreateProductVariantWithInventory(): void
    {
        $productOptions = $this->product->getOptions();
        $productOptionValueIds = $productOptions->map(
            fn ($o) => collect($o->getValues())->map(fn ($v) => $v->getId())->random()
        )->toArray();

        $newOption = new ProductOption();
        $newOption
            ->setCode('newOption')
            ->setName('newOption')
            ->addValue(
                (new ProductOptionValue())
                    ->setValue('option value 1')
                    ->setAttributes(['key' => 'ASD', 'value' => 'DSA'])
            );

        $this->product->addOption($newOption);

        $this->manager->persist($newOption);
        $this->manager->persist($this->product);
        $this->manager->flush();

        $productOptionValueIds[] = $newOption->getValues()->first()->getId();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
                "sellerCode" => 'sellerCode',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
        self::assertArrayHasKey('id', $response['results']);
    }

    public function testItCanNotCreateInventoryDueToProductIdentifierConstraint(): void
    {
        $categoryProductIdentifier = new CategoryProductIdentifier();
        $categoryProductIdentifier->setRequired(true);

        $this->product->getCategory()
                      ->setCategoryProductIdentifier($categoryProductIdentifier);

        $this->manager->persist($categoryProductIdentifier);

        $productOptions = $this->product->getOptions();
        $productOptionValueIds = $productOptions->map(
            fn ($o) => collect($o->getValues())->map(fn ($v) => $v->getId())->random()
        )->toArray();

        $newOption = new ProductOption();
        $newOption
            ->setCode('newOption')
            ->setName('newOption')
            ->addValue(
                (new ProductOptionValue())
                    ->setValue('option value 1')
                    ->setAttributes(['key' => 'ASD', 'value' => 'DSA'])
            );

        $this->product->addOption($newOption);

        $this->manager->persist($newOption);
        $this->manager->persist($this->product);
        $this->manager->flush();
        $this->manager->clear();

        $productOptionValueIds[] = $newOption->getValues()->first()->getId();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertResponseStatusCodeSame(422);

        self::assertContains(
            'Chosen product has no product identifier!',
            data_get($this->getControllerResponse(), 'results.product')
        );
    }

    public function testFailedValidationDueToNotAllowedStatus(): void
    {
        $productOptions = $this->product->getOptions();
        $productOptionValueIds = $productOptions->map(
            fn ($o) => collect($o->getValues())->map(fn ($v) => $v->getId())->random()
        )->toArray();

        $newOption = new ProductOption();
        $newOption
            ->setCode('newOption')
            ->setName('newOption')
            ->addValue(
                (new ProductOptionValue())
                    ->setValue('option value 1')
                    ->setAttributes(['key' => 'ASD', 'value' => 'DSA'])
            );

        $this->product->addOption($newOption)
                      ->setStatus(ProductStatusDictionary::WAITING_FOR_ACCEPT);

        $this->manager->persist($newOption);
        $this->manager->persist($this->product);
        $this->manager->flush();

        $productOptionValueIds[] = $newOption->getValues()->first()->getId();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
        self::assertArrayHasKey('product', $response['results']);
        self::assertContains('Chosen product should be in confirmed or unavailable.', $response['results']['product']);
    }

    public function testItCanCreateInventory(): void
    {
        $inventory = $this->manager->getRepository(Inventory::class)->findOneBy([
            'isActive' => 0,
        ]);

        $variant = $inventory->getVariant();

        $productOptionValueIds = $variant->getOptionValues()
            ->map(fn (ProductOptionValue $optionValue) => $optionValue->getId())
            ->toArray();

        $variant->getProduct()->setStatus(ProductStatusDictionary::CONFIRMED);

        $this->manager->remove($inventory);

        $this->manager->flush();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $variant->getProduct()->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
        self::assertArrayHasKey('id', $response['results']);
    }

    public function testValidationFailureCreateProductVariantWithInventoryWhenMaxPurchasePerOrderIsNegative(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 1,
                "price" => 30000,
                "finalPrice" => 30000,
                "isActive" => 1,
                "maxPurchasePerOrder" => -2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('maxPurchasePerOrder', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals(
            'This value should be either positive or zero.',
            $response['results']['maxPurchasePerOrder'][0]
        );
    }

    public function testValidationFailureCreateProductVariantWithInventoryWhenFinalPriceIsNegative(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 1,
                "price" => 30000,
                "finalPrice" => -30000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('finalPrice', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals('This value should be either positive or zero.', $response['results']['finalPrice'][0]);
    }

    public function testValidationFailureCreateProductVariantWithInventoryWhenPriceIsNegative(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 1,
                "price" => -30000,
                "finalPrice" => 30000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('price', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals('This value should be either positive or zero.', $response['results']['price'][0]);
    }

    public function testValidationFailureCreateProductVariantWithInventoryWhenStockIsNegative(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => -1,
                "price" => 30000,
                "finalPrice" => 30000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('stock', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals('This value should be either positive or zero.', $response['results']['stock'][0]);
    }

    public function testValidationFailureCreateProductVariantWithInventoryForPriceValidation(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 30000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('price', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals(
            'This value should be greater than or equal to "40000".',
            $response['results']['price'][0]
        );
    }

    public function testValidationFailureCreateProductVariantWithInventory(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "product" => $this->product->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('optionValues', $response['results']);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertEquals('This value should not be blank.', $response['results']['optionValues'][0]);
    }

    public function testItFailsOnCreateProductVariantWithInventoryWhenTheirCombinationIsNotUnique(): void
    {
        $this->client->disableReboot();

        $inventory = $this->manager->getRepository(Inventory::class)->findOneBy([
            'isActive' => 0,
        ]);

        $variant = $inventory->getVariant();

        $productOptionValueIds = $variant->getOptionValues()
            ->map(fn (ProductOptionValue $optionValue) => $optionValue->getId())
            ->toArray();

        $variant->getProduct()->setStatus(ProductStatusDictionary::CONFIRMED);

        $this->manager->remove($inventory);
        $this->manager->flush();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $variant->getProduct()->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        // Duplicate request
        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $variant->getProduct()->getId(),
                "seller" => $this->seller->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testItFailsOnCreateProductVariantWithInventoryWhenOptionValuesHasSameOption(): void
    {
        $productOptionValueIds = array_map(
            static fn ($optionValue) => $optionValue->getId(),
            $this->productOptionValues
        );

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('Selected option values is invalid!', $response['detail']);
    }

    public function testItFailsOnCreateProductVariantWithInventoryWhenOptionValuesIsNotMatchToProduct(): void
    {
        $productOptions = $this->product->getOptions();
        $productOptionValueIds = $productOptions->map(
            fn ($o) => collect($o->getValues())->map(fn ($v) => $v->getId())->random()
        )->toArray();

        array_shift($productOptionValueIds);

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('Product options not set!', $response['detail']);
    }

    public function testHasInventoryExistenceExceptionOnCreateProductVariantWithInventory(): void
    {
        $inventory = $this->manager->getRepository(Inventory::class)->findOneBy([
            'isActive' => 0,
        ]);

        $variant = $inventory->getVariant();

        $productOptionValueIds = $variant->getOptionValues()
            ->map(fn (ProductOptionValue $optionValue) => $optionValue->getId())
            ->toArray();

        $variant->getProduct()->setStatus(ProductStatusDictionary::CONFIRMED);

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $variant->getProduct()->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 1,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('Inventory Exists!', $response['detail']);
    }

    public function testHasInvalidInventoryLeadTimeExceptionOnCreateProductVariantWithInventory(): void
    {
        $productOptions = $this->product->getOptions();
        $productOptionValueIds = $productOptions->map(
            fn ($o) => collect($o->getValues())->map(fn ($v) => $v->getId())->random()
        )->toArray();

        $newOption = new ProductOption();
        $newOption
            ->setCode('newOption')
            ->setName('newOption')
            ->addValue(
                (new ProductOptionValue())
                    ->setValue('option value 1')
                    ->setAttributes(['key' => 'ASD', 'value' => 'DSA'])
            );

        $this->product->addOption($newOption);

        $this->manager->persist($newOption);
        $this->manager->persist($this->product);
        $this->manager->flush();

        $productOptionValueIds[] = $newOption->getValues()->first()->getId();

        $client = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.product_variants.store'),
            [
                "optionValues" => $productOptionValueIds,
                "product" => $this->product->getId(),
                "stock" => 10,
                "price" => 50000,
                "finalPrice" => 40000,
                "isActive" => 1,
                "maxPurchasePerOrder" => 2,
                "suppliesIn" => 10,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('Lead time has invalid value!', $response['detail']);
    }

    public function testUpdateShouldThrowExceptionIfProductIsNotInConfirmedOrUnavailableState(): void
    {
        $this->variant->getProduct()->setStatus(ProductStatusDictionary::REJECTED);

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            $this->route('seller.product_variants.inventory.update', ['id' => $this->variant->getId()]),
            [
                'stock' => 20,
                'price' => 150000,
                'finalPrice' => 140000,
                'maxPurchasePerOrder' => 2,
                'suppliesIn' => 1,
                'isActive' => true,
                'sellerCode' => 'sellerCode',
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
