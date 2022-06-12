<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Service\CustomerAddress\DefaultCustomerAddressService;
use App\Service\CustomerAddress\Exceptions\UnexpectedCustomerAddressException;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\StoreOrderAddressStage;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StoreOrderAddressStageTest extends MockeryTestCase
{
    private CreateOrderPayload $storeOrderPayload;

    private StoreOrderAddressStage $storeOrderAddressStage;

    private DefaultCustomerAddressService $customerAddressService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeOrderPayload = new CreateOrderPayload(
            m::mock(EntityManagerInterface::class),
            $this->createFakeCartWithCartItem(),
            $this->createFakeCustomerAddress(),
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $this->storeOrderPayload->setOrder(new Order());

        $this->customerAddressService = m::mock(DefaultCustomerAddressService::class);
        $this->customerAddressService->shouldReceive('set')->once();

        $this->storeOrderAddressStage = new StoreOrderAddressStage($this->customerAddressService);
    }

    protected function tearDown(): void
    {
        unset($this->storeOrderPayload, $this->storeOrderAddressStage, $this->customerAddressService);
    }

    /**
     * @throws UnexpectedCustomerAddressException
     */
    public function testItCanStoreOrderAddressSuccessfully(): void
    {
        $orderAddress = $this->storeOrderAddressStage
            ->__invoke($this->storeOrderPayload)
            ->getOrder()
            ->getOrderAddress();

        self::assertNotNull($orderAddress->getCustomerAddress());
        self::assertNotNull($orderAddress->getCity());
        self::assertNotNull($orderAddress->getCoordinates());
        self::assertNotNull($orderAddress->getDistrict());
        self::assertNotNull($orderAddress->getFamily());
        self::assertNotNull($orderAddress->getName());
        self::assertNotNull($orderAddress->getNationalCode());
        self::assertNotNull($orderAddress->getFullAddress());
        self::assertNotNull($orderAddress->getNumber());
        self::assertNotNull($orderAddress->getPhone());
        self::assertNotNull($orderAddress->getPostalCode());
        self::assertNotNull($orderAddress->getUnit());
    }

    private function createFakeCartWithCartItem(): Cart
    {
        $cart = new Cart();
        $cartItem = new CartItem();
        $cartItem->setGrandTotal(10);
        $cartItem->setSubtotal(10);
        $cartItem->setQuantity(1);
        $cartItem->setPrice(10);
        $cartItem->setInventory(new Inventory());
        $cart->addCartItem($cartItem);
        $cart->setGrandTotal(10);
        $cart->setSubtotal(20);

        return $cart;
    }

    private function createFakeCustomerAddress(): CustomerAddress
    {
        return (new CustomerAddress())
            ->setCity(new City())
            ->setCoordinates(m::mock(AbstractPoint::class))
            ->setDistrict(new District())
            ->setFamily('Family')
            ->setName('Name')
            ->setNationalCode(1234567899)
            ->setFullAddress('FullAddress')
            ->setNumber(210)
            ->setMobile('0912123456789')
            ->setPostalCode(1234567899)
            ->setUnit(2)
            ->setCustomer(new Customer());
    }
}
