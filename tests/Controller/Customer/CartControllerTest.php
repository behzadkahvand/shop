<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\CityDictionary;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Entity\PromotionCoupon;
use App\Entity\PromotionRule;
use App\Repository\CustomerRepository;
use App\Repository\PromotionCouponRepository;
use App\Service\Cart\CartService;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\Promotion\Rule\MinimumBasketTotalRuleType;
use App\Tests\Controller\BaseControllerTestCase;
use App\Tests\Controller\Traits\PromotionTrait;

class CartControllerTest extends BaseControllerTestCase
{
    use PromotionTrait;

    private ?Inventory $inventory;

    private ?Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventory = $this->manager->getRepository(Inventory::class)->findOneBy([]);
        $this->inventory->setMaxPurchasePerOrder(20);

        $this->cart = $this->manager->getRepository(Cart::class)->findOneBy([]);
        $cartItem = $this->cart->getCartItems()[0]->setInventory($this->inventory)->setQuantity(1);
        $this->cart->getCartItems()->clear();
        $this->cart->addCartItem($cartItem);

        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        unset($this->inventory, $this->cart);
        AbstractPartialShipment::resetId();
        parent::tearDown();
    }

    public function testItCanAddAnItemToCartForTheFirstTimeAsUser(): void
    {
        $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.cart.add', ['id' => $this->inventory->getId()]),
        );

        self::assertResponseIsSuccessful();
    }

    public function testItCanIncreaseQuantityOfAnItemAsGuestUser(): void
    {
        // We removed Guest feature at the moment
        self::markTestSkipped();

        $this->sendRequest(
            'POST',
            $this->route('customer.cart.add', ['id' => $this->inventory->getId()]),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();
    }

    public function testItCanIncreaseQuantityOfAnItemAsUser(): void
    {
        $this->client->disableReboot();

        $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->route('customer.cart.add', ['id' => $this->inventory->getId()]),
        );

        //@TODO Fix test, must be removed this part
        $this->sendRequest(
            'DELETE',
            $this->route('customer.cart.remove', ['id' => $this->inventory->getId()]),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        $this->sendRequest(
            'POST',
            $this->route('customer.cart.add', ['id' => $this->inventory->getId()]),
        );

        self::assertResponseIsSuccessful();
    }

    public function testItCanChangeQuantityOfAnItemAsGuestUser(): void
    {
        // We removed Guest feature at the moment
        self::markTestSkipped();

        $this->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => 5],
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subtotal', $results);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('customer', $results);
        self::assertArrayHasKey('cartItems', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItCanChangeQuantityOfAnItemAsUser(): void
    {
        $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => 5]
        );

        self::assertResponseIsSuccessful();

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subtotal', $results);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('customer', $results);
        self::assertArrayHasKey('cartItems', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItFailsOnValidationWhenChangeQuantityOfAnItemAsGuestUser(): void
    {
        // We removed Guest feature at the moment
        self::markTestSkipped();

        $this->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => -1],
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('quantity', $result['results']);
        self::assertContains('This value should be positive.', $result['results']['quantity']);
    }

    public function testItFailsOnValidationWhenChangeQuantityOfAnItemAsUser(): void
    {
        $response = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => -1]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('quantity', $result['results']);
        self::assertContains('This value should be positive.', $result['results']['quantity']);
    }

    public function testItFailsWhenMaxPurchasePerOrderExceededOnChangeQuantityOfAnItemAsGuestUser(): void
    {
        // We removed Guest feature at the moment
        self::markTestSkipped();

        $response = $this->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => 999999],
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testItFailsWhenMaxPurchasePerOrderExceededOnChangeQuantityOfAnItemAsUser(): void
    {
        $response = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.change', ['id' => $this->inventory->getId()]),
            ['quantity' => 999999]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testItCanRemoveAnItemAsGuestUser(): void
    {
        // We removed Guest feature at the moment
        self::markTestSkipped();

        $this->sendRequest(
            'DELETE',
            $this->route('customer.cart.remove', ['id' => $this->inventory->getId()]),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subtotal', $results);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('customer', $results);
        self::assertArrayHasKey('cartItems', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItCanRemoveAnItemAsUser(): void
    {
        $this->loginAs($this->customer)->sendRequest(
            'DELETE',
            $this->route('customer.cart.remove', ['id' => $this->inventory->getId()])
        );

        self::assertResponseIsSuccessful();

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subtotal', $results);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('customer', $results);
        self::assertArrayHasKey('cartItems', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItFailsOnRemoveAnItemAsGuestUser(): void
    {
        self::markTestSkipped();

        $response = $this->sendRequest(
            'DELETE',
            $this->route('customer.cart.remove', ['id' => 10000000000]),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnRemoveAnItemAsUser(): void
    {
        $response = $this->loginAs($this->customer)->sendRequest(
            'DELETE',
            $this->route('customer.cart.remove', ['id' => 10000000000])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItCanViewEmptyCartAsGuestUser(): void
    {
        $this->cart->getCartItems()->clear();
        $this->cart->setCustomer(null);
        $this->manager->flush();

        $this->sendRequest(
            'GET',
            $this->route('customer.cart.show'),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();

        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subTotal', $results);
        self::assertArrayHasKey('minimumCart', $results);
        self::assertArrayHasKey('isPossibleToOrder', $results);
        self::assertIsBool($results['isPossibleToOrder']);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('shipments', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItCanViewEmptyCartAsUser(): void
    {
        $this->cart->getCartItems()->clear();
        $this->manager->flush();

        $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.cart.show')
        );

        self::assertResponseIsSuccessful();
        $results = data_get($this->getControllerResponse(), 'results');

        self::assertArrayHasKey('id', $results);
        self::assertArrayHasKey('subTotal', $results);
        self::assertArrayHasKey('minimumCart', $results);
        self::assertArrayHasKey('isPossibleToOrder', $results);
        self::assertIsBool($results['isPossibleToOrder']);
        self::assertArrayHasKey('grandTotal', $results);
        self::assertArrayHasKey('messages', $results);
        self::assertArrayHasKey('shipments', $results);
        self::assertArrayHasKey('itemsCount', $results);
    }

    public function testItCanViewUnchangedCartAsGuestUser(): void
    {
        $this->sendRequest(
            'GET',
            $this->route('customer.cart.show'),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();
    }

    public function testItCanViewUnchangedCartAsUser(): void
    {
        $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.cart.show')
        );

        self::assertResponseIsSuccessful();
    }

    public function testItCanViewUpdatedCartAsGuestUser(): void
    {
        // Change item price
        $this->inventory->setPrice(999);

        // We should do this to ensure our cart isn't belong to another user
        $this->cart->setCustomer(null);
        $this->manager->flush();

        // View the cart
        $this->sendRequest(
            'GET',
            $this->route('customer.cart.show'),
            null,
            [],
            ['HTTP_X-Cart' => $this->cart->getId()]
        );

        self::assertResponseIsSuccessful();

        $cart = data_get($this->getControllerResponse(), 'results');
        $shipmentItems = array_merge(...array_map(static function ($shipmentItem) {
            return $shipmentItem['items'];
        }, data_get($cart, 'shipments')));

        $cartItem = array_values(array_filter($shipmentItems, static function ($shipmentItem) {
            return $shipmentItem['price'] === 999;
        }));

        // Response should contains messages about updated items
        self::assertEquals(999, $cartItem[0]['subTotal']);
        self::assertNotEmpty($cartItem[0]['messages']);
        self::assertNotEmpty($cart['messages']);
    }

    public function testItCanViewUpdatedCartAsUser(): void
    {
        // Change item price
        $this->inventory->setPrice(999);
        $this->manager->flush();

        // View the cart
        $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.cart.show')
        );

        self::assertResponseIsSuccessful();

        $cart = data_get($this->getControllerResponse(), 'results');
        $shipmentItems = array_merge(...array_map(static function ($shipmentItem) {
            return $shipmentItem['items'];
        }, data_get($cart, 'shipments')));

        $cartItem = array_values(array_filter($shipmentItems, static function ($shipmentItem) {
            return $shipmentItem['price'] === 999;
        }));

        // Response should contains messages about updated items
        self::assertEquals(999, $cartItem[0]['price']);
        self::assertNotEmpty($cartItem[0]['messages']);
        self::assertNotEmpty($cart['messages']);
    }

    public function testCalculateShipmentsWithTehranAsDefaultCity(): void
    {
        $this->cart->setCustomer(null);
        $this->manager->flush();

        $this->sendRequest('GET', '/cart', null, [], ['HTTP_X-Cart' => $this->cart->getId()]);

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertArrayHasKey('itemsCount', $result['results']);
        self::assertIsArray($result['results']);

        self::assertArrayHasKeysAndValues([
            'id' => $this->cart->getId(),
            'subTotal' => 100000,
            'grandTotal' => 90000,
            'shipmentsTotal' => 0,
            'total' => 10000,
            'minimumCart' => 100000,
            'isPossibleToOrder' => false,
            'itemsCount' => 1,
            'coupon' => null,
            'discounts' => [],
        ], $result['results']);

        self::assertArrayHasKey('shipments', $result['results']);
        self::assertNotEmpty($result['results']['shipments']);
        foreach (data_get($result, 'results.shipments') as $r) {
            self::assertArrayHasKeysAndValues([
                'id' => 1,
                'shippingMethod' => 'اکسپرس تیمچه - عادی',
                'price' => ['subTotal' => 0, 'grandTotal' => 0],
                'description' => null,
            ], $r);

            self::assertArrayHasKey('items', $r);
            self::assertIsArray($r['items']);

            foreach ($r['items'] as $item) {
                self::assertArrayHasKey('id', $item['inventory']['variant']['product'] ?? []);
            }

            self::assertArrayHasKeysAndValues([
                'price' => 100000,
                'subTotal' => 100000,
                'grandTotal' => 90000,
                'quantity' => 1,
            ], $r['items'][0]);

            self::assertArrayHasKey('deliveryDates', $r);
            self::assertCount(4, $r['deliveryDates']);
            foreach ($r['deliveryDates'] as $i => $delivery) {
                self::assertArrayHasKeys(['date', 'periods'], $delivery);
                self::assertIsArray($delivery['periods']);
                self::assertCount(3, $delivery['periods']);

                foreach ($delivery['periods'] as $j => $period) {
                    self::assertArrayHasKeys(['start', 'end', 'selectable'], $period);
                    self::assertIsBool($period['selectable']);
                }
            }
        }
    }

    public function testCalculateShipmentsForTehran(): void
    {
        /** @var QueryBuilderFilterService $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class);

        $cart = $this->manager->getRepository(Cart::class)->findOneBy([]);

        $address = $queryBuilder->filter(CustomerAddress::class, [
            'filter' => [
                'city.name' => CityDictionary::TEHRAN_NAME,
                'customer.id' => $cart->getCustomer()->getId(),
            ],
        ])->getQuery()->getResult()[0];

        $this->loginAs($this->customer)
            ->sendRequest('GET', "/cart?address={$address->getId()}", null, [], [
                'HTTP_X-Cart' => $this->cart->getId(),
            ]);

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);

        $cart = $result['results'];
        self::assertArrayHasKeys([
            'id',
            'subTotal',
            'grandTotal',
            'messages',
            'shipments',
            'shipmentsTotal',
            'total',
            'itemsCount',
            'coupon',
            'discounts',
        ], $cart);

        foreach (data_get($result, 'results.shipments') as $r) {
            self::assertArrayHasKeys(['id', 'shippingMethod', 'price', 'items', 'description', 'deliveryDates'], $r);

            self::assertNull($r['description']);
            self::assertIsArray($r['items']);
            self::assertNotEmpty($r['items']);
            foreach ($r['items'] as $item) {
                self::assertArrayHasKey('id', $item['inventory']['variant']['product'] ?? []);
            }

            self::assertCount(4, $r['deliveryDates']);
            foreach ($r['deliveryDates'] as $i => $delivery) {
                self::assertArrayHasKeys(['date', 'periods'], $delivery);
                self::assertIsArray($delivery['periods']);

                foreach ($delivery['periods'] as $j => $period) {
                    self::assertArrayHasKeys(['start', 'end', 'selectable'], $period);
                    self::assertIsBool($period['selectable']);
                }
            }
        }
    }

    public function testCalculateShipmentsForOtherCities(): void
    {
        /** @var QueryBuilderFilterService $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class);

        $cart = $this->manager->getRepository(Cart::class)->findOneBy([]);

        $address = $queryBuilder->filter(CustomerAddress::class, [
            'filter' => [
                'city.name' => ['neq' => CityDictionary::TEHRAN_NAME],
                'customer.id' => $cart->getCustomer()->getId(),
            ],
        ])->getQuery()->getResult()[0];

        $this->loginAs($this->customer)
            ->sendRequest(
                'GET',
                "/cart?address={$address->getId()}",
                null,
                [],
                ['HTTP_X-Cart' => $this->cart->getId()]
            );

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);

        $cart = $result['results'];
        self::assertArrayHasKeys([
            'id',
            'subTotal',
            'grandTotal',
            'messages',
            'shipments',
            'shipmentsTotal',
            'total',
            'itemsCount',
            'coupon',
            'discounts',
        ], $cart);

        foreach (data_get($result, 'results.shipments') as $r) {
            self::assertArrayHasKeys(['id', 'shippingMethod', 'price', 'items', 'description', 'deliveryDates'], $r);
            self::assertNotNull($r['description']);
            self::assertMatchesRegularExpression('/^[^\d]+? \d+? [^\d]+? \d+? [^\d]+?$/', $r['description']);

            self::assertIsArray($r['items']);
            self::assertNotEmpty($r['items']);
            foreach ($r['items'] as $item) {
                self::assertArrayHasKey('id', $item['inventory']['variant']['product'] ?? []);
            }

            self::assertArrayHasKey('deliveryDates', $r);
            self::assertIsArray($r['deliveryDates']);
            self::assertEmpty($r['deliveryDates']);
        }
    }

    public function testApplyCouponToCart(): void
    {
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $customer->getCart()->getCartItems()->first()->setGrandTotal(5000);

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer);

        $this->loginAs($customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.coupon'),
            ['promotionCoupon' => 'first_order', 'address' => $customer->getAddresses()->first()->getId()]
        );

        self::assertResponseIsSuccessful();
    }

    public function testClearCartCoupon(): void
    {
        /** @var Customer $customer */
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer, $customer->getAddresses()->first());
        $cart = $customer->getCart();
        $cart->setPromotionCoupon($coupon);
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);
        $cartService->view(['address' => $customer->getAddresses()->first()], $cart);

        self::assertNotNull($cart->getPromotionCoupon());

        $this->loginAs($customer)->sendRequest(
            'DELETE',
            $this->route('customer.cart.coupon.delete')
        );

        self::assertResponseIsSuccessful();
        self::assertNull($cart->getPromotionCoupon());
    }

    public function testApplyCouponToCartFailedDueToWrongCode(): void
    {
        /** @var Customer $customer */
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer);

        $response = $this->loginAs($customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.coupon'),
            ['promotionCoupon' => 'first_order_wrong', 'address' => $customer->getAddresses()->first()]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testApplyCouponToCartFailedDueToCaseSensitivity(): void
    {
        /** @var Customer $customer */
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $customer->getCart()->getItems()->first()->setGrandTotal(6000);
        $this->manager->flush();

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer);

        $this->loginAs($customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.coupon'),
            ['promotionCoupon' => 'FIRST_ORDER', 'address' => $customer->getAddresses()->first()->getId()]
        );

        self::assertResponseIsSuccessful();
    }

    public function testApplyCouponToCartFailedDueToMinimumBasketTotal(): void
    {
        /** @var Customer $customer */
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $customer->getCart()->getItems()->first()->setGrandTotal(6000);
        $this->manager->flush();

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'everyone']);
        $promotion = $coupon->getPromotion();
        /** @var PromotionRule $categoryRule */
        $minimumBasketTotalRule = (new PromotionRule())->setType('minimum_basket_total');
        $minimumBasketTotalRule->setConfiguration([
            MinimumBasketTotalRuleType::CONFIGURATION_BASKET_TOTAL => 1000000000,
        ]);
        $promotion->getRules()->clear();
        $promotion->addRule($minimumBasketTotalRule);
        $this->manager->persist($minimumBasketTotalRule);
        $this->manager->flush();

        $response = $this->loginAs($customer)->sendRequest(
            'PATCH',
            $this->route('customer.cart.coupon'),
            ['promotionCoupon' => 'everyone', 'address' => $customer->getAddresses()->first()->getId()]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('promotionCoupon', $response['results']);
        self::assertArrayHasKey('errors', $response['results']['promotionCoupon']);
        self::assertEquals(
            'حداقل میزان سبد خرید رعایت نشده است. ',
            $response['results']['promotionCoupon']['errors'][0]
        );
    }

    public function testViewCartAndApplyCoupon(): void
    {
        /** @var Customer $customer */
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $address = $customer->getAddresses()->first();
        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer, $address);

        $cart = $customer->getCart();
        $cart->setPromotionCoupon($coupon);
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);

        $this->loginAs($customer)->sendRequest(
            'GET',
            $this->route('customer.cart.show', ['address' => $address->getId()])
        );

        self::assertResponseIsSuccessful();

        $responseData = $this->getControllerResponse();
        self::assertEquals(510000, $responseData['results']['grandTotal']);
    }
}
