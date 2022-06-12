<?php

namespace App\Tests\Unit\Service\PartialShipment;

use App\DTO\Customer\CreateShipmentData;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\Zone;
use App\Service\PartialShipment\Factory\PartialShipmentFactory;
use App\Service\PartialShipment\Grouping\PartialShipmentItemGrouperInterface;
use App\Service\PartialShipment\PartialShipmentService;
use App\Service\PartialShipment\PartialShipmentValidator;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use App\Service\Zones\ZoneDetector\ZoneDetector;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Class PartialShipmentServiceTest
 */
final class PartialShipmentServiceTest extends MockeryTestCase
{
    public function testItCreatePartialShipmentsFromCart(): void
    {
        $cartItems = array_map(
            function (Inventory $inventory) {
                $cartItem = new CartItem();
                $price = random_int(100000, 200000);
                $cartItem->setInventory($inventory)
                         ->setQuantity(random_int(1, 5))
                         ->setSubtotal($price)
                         ->setGrandTotal($price)
                         ->setQuantity(random_int(1, 5));

                return $cartItem;
            },
            $this->getInventories()
        );

        $cart = new Cart();

        foreach ($cartItems as $cartItem) {
            $cart->addCartItem($cartItem);
        }

        $customerAddress = Mockery::mock(CustomerAddress::class);

        $partialShipment = Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('calculate')
                        ->with(Mockery::type(\DateTimeInterface::class));

        $zone         = Mockery::mock(Zone::class);
        $zoneDetector = Mockery::mock(ZoneDetector::class);
        $zoneDetector->shouldReceive('getZoneForCustomerAddress')
                     ->once()
                     ->with($customerAddress)
                     ->andReturn($zone);

        $isExpressDelivery = false;

        $factory = Mockery::mock(PartialShipmentFactory::class);
        $factory->shouldReceive('create')
                ->with(
                    Mockery::type(\DateTimeInterface::class),
                    $zone,
                    Mockery::type('array'),
                    $isExpressDelivery
                )
                ->andReturn($partialShipment);

        $itemGrouper = Mockery::mock(PartialShipmentItemGrouperInterface::class);
        $itemGrouper->shouldReceive('group')
                    ->once()
                    ->with(Mockery::type('array'))
                    ->andReturnUsing(function (array $items) {
                        return [$items];
                    });

        $partialShipmentService = new PartialShipmentService(
            $zoneDetector,
            $itemGrouper,
            $factory,
            Mockery::mock(PartialShipmentValidator::class)
        );

        $partialShipments  = $partialShipmentService->createFromCart($cart, $customerAddress, $isExpressDelivery);

        self::assertIsArray($partialShipments);

        foreach ($partialShipments as $partialShipment) {
            self::assertInstanceOf(AbstractPartialShipment::class, $partialShipment);
        }
    }

    public function testIfFailToGetPartialShipmentsIfSelectedValuesDoesNotContainsPartialShipmentId(): void
    {
        $inventories = $this->getInventories();
        $cartItems   = array_map(
            function (Inventory $inventory) {
                $cartItem = new CartItem();
                $price = random_int(100000, 200000);
                $cartItem->setInventory($inventory)
                         ->setQuantity(random_int(1, 5))
                         ->setSubtotal($price)
                         ->setGrandTotal($price)
                         ->setQuantity(random_int(1, 5));

                return $cartItem;
            },
            $inventories
        );

        $cart = new Cart();

        foreach ($cartItems as $cartItem) {
            $cart->addCartItem($cartItem);
        }

        $customerAddress = Mockery::mock(CustomerAddress::class);

        $partialShipment = Mockery::mock(ExpressPartialShipment::class);

        $partialShipment->shouldReceive('getId')
                        ->withNoArgs()
                        ->andReturn(random_int(100, 200));

        $partialShipment->shouldReceive('shouldBeValidated')
                        ->withNoArgs()
                        ->andReturn(true);

        $isExpressDelivery = false;

        $zone = Mockery::mock(Zone::class);

        $factory = Mockery::mock(PartialShipmentFactory::class);
        $factory->shouldReceive('create')
                ->with(
                    Mockery::type(\DateTimeInterface::class),
                    $zone,
                    Mockery::type('array'),
                    $isExpressDelivery
                )
                ->andReturn($partialShipment);

        $zoneDetector = Mockery::mock(ZoneDetector::class);
        $zoneDetector->shouldReceive('getZoneForCustomerAddress')
                     ->once()
                     ->with($customerAddress)
                     ->andReturn($zone);

        $itemGrouper = Mockery::mock(PartialShipmentItemGrouperInterface::class);
        $itemGrouper->shouldReceive('group')
                    ->once()
                    ->with(Mockery::type('array'))
                    ->andReturnUsing(function (array $items) {
                        return [$items];
                    });

        $partialShipmentService = new PartialShipmentService(
            $zoneDetector,
            $itemGrouper,
            $factory,
            Mockery::mock(PartialShipmentValidator::class)
        );

        try {
            $partialShipmentService->getPartialShipments($cart, $customerAddress, [], $isExpressDelivery);
        } catch (ValidationFailedException $e) {
            foreach (iterator_to_array($e->getViolations()) as $violation) {
                self::assertMatchesRegularExpression(
                    '/^The delivery date of shipment with id \d+ is not selected\.$/',
                    $violation->getMessage()
                );
            }
        }
    }

    public function testIfFailToGetPartialShipmentsIfPartialShipmentIsNotValid(): void
    {
        $inventories = $this->getInventories();
        $cartItems   = array_map(
            function (Inventory $inventory) {
                $cartItem = new CartItem();
                $price = random_int(100000, 200000);
                $cartItem->setInventory($inventory)
                         ->setQuantity(random_int(1, 5))
                         ->setSubtotal($price)
                         ->setGrandTotal($price)
                         ->setQuantity(random_int(1, 5));

                return $cartItem;
            },
            $inventories
        );

        $cart = new Cart();

        foreach ($cartItems as $cartItem) {
            $cart->addCartItem($cartItem);
        }

        $customerAddress = Mockery::mock(CustomerAddress::class);

        $validator = Mockery::mock(PartialShipmentValidator::class);

        $partialShipment = Mockery::mock(ExpressPartialShipment::class);

        $partialShipment->shouldReceive('getId')
                        ->withNoArgs()
                        ->andReturn(random_int(100, 200));

        $validator->shouldReceive('validate')
                  ->with($partialShipment, Mockery::type(\DateTimeInterface::class))
                  ->andReturn(new ConstraintViolationList([Mockery::mock(ConstraintViolation::class)]));

        $isExpressDelivery = false;

        $zone = Mockery::mock(Zone::class);

        $factory = Mockery::mock(PartialShipmentFactory::class);
        $factory->shouldReceive('create')
                ->with(
                    Mockery::type(\DateTimeInterface::class),
                    $zone,
                    Mockery::type('array'),
                    $isExpressDelivery
                )
                ->andReturn($partialShipment);

        $zoneDetector = Mockery::mock(ZoneDetector::class);
        $zoneDetector->shouldReceive('getZoneForCustomerAddress')
                     ->twice()
                     ->with($customerAddress)
                     ->andReturn($zone);

        $itemGrouper = Mockery::mock(PartialShipmentItemGrouperInterface::class);
        $itemGrouper->shouldReceive('group')
                    ->twice()
                    ->with(Mockery::type('array'))
                    ->andReturnUsing(function (array $items) {
                        return [$items];
                    });

        $partialShipmentService = new PartialShipmentService(
            $zoneDetector,
            $itemGrouper,
            $factory,
            $validator
        );

        $selectedValues = array_map(
            function (AbstractPartialShipment $partialShipment) {
                return (new CreateShipmentData())->setId($partialShipment->getId())->setDeliveryDate(new \DateTime());
            },
            $partialShipmentService->createFromCart($cart, $customerAddress, false)
        );

        $this->expectException(ValidationFailedException::class);

        $isExpressDelivery = false;
        $partialShipmentService->getPartialShipments($cart, $customerAddress, $selectedValues, $isExpressDelivery);
    }

    public function testItGetPartialShipments(): void
    {
        $inventories = $this->getInventories();
        $cartItems   = array_map(
            function (Inventory $inventory) {
                $cartItem = new CartItem();
                $price = random_int(100000, 200000);
                $cartItem->setInventory($inventory)
                         ->setQuantity(random_int(1, 5))
                         ->setSubtotal($price)
                         ->setGrandTotal($price)
                         ->setQuantity(random_int(1, 5));

                return $cartItem;
            },
            $inventories
        );

        $cart = new Cart();

        foreach ($cartItems as $cartItem) {
            $cart->addCartItem($cartItem);
        }

        $customerAddress = Mockery::mock(CustomerAddress::class);

        $partialShipment1Id = random_int(100, 200);

        $partialShipment1 = Mockery::mock(ExpressPartialShipment::class);
        $partialShipment1->shouldReceive('getId')
                         ->withNoArgs()
                         ->andReturn($partialShipment1Id);

        $partialShipment1->shouldReceive('shouldBeValidated')
                         ->withNoArgs()
                         ->andReturn(true);

        $partialShipmentItem = Mockery::mock(PartialShipmentItem::class);
        $partialShipmentItem->shouldReceive('getShippingCategory')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(Mockery::mock(ShippingCategory::class));

        $partialShipment1->shouldReceive('freeze')
                         ->with(Mockery::type(\DateTimeInterface::class))
                         ->andReturn(
                             new BaseFreezedPartialShipment(
                                 [$partialShipmentItem],
                                 Mockery::mock(ShippingMethod::class),
                                 new PartialShipmentPrice(1000, 1000),
                                 new \DateTime(),
                                 'order_shipment#1'
                             )
                         );

        $partialShipment2Id = random_int(100, 200);

        $partialShipment2 = Mockery::mock(ExpressPartialShipment::class);
        $partialShipment2->shouldReceive('getId')
                         ->withNoArgs()
                         ->andReturn($partialShipment2Id);

        $partialShipment2->shouldReceive('shouldBeValidated')
                         ->withNoArgs()
                         ->andReturn(false);

        $partialShipment2->shouldReceive('freeze')
                         ->with(Mockery::type(\DateTimeInterface::class))
                         ->andReturn(
                             new BaseFreezedPartialShipment(
                                 [clone $partialShipmentItem],
                                 Mockery::mock(ShippingMethod::class),
                                 new PartialShipmentPrice(1000, 1000),
                                 new \DateTime(),
                                 'order_shipment#1'
                             )
                         );

        $partialShipment2->shouldReceive('getCalculatedDeliveryDate')
                         ->withNoArgs()
                         ->andReturn(new \DateTimeImmutable());

        $isExpressDelivery = false;

        $zone = Mockery::mock(Zone::class);

        $factory = Mockery::mock(PartialShipmentFactory::class);
        $factory->shouldReceive('create')
                ->with(
                    Mockery::type(\DateTimeInterface::class),
                    $zone,
                    Mockery::type('array'),
                    $isExpressDelivery
                )
                ->andReturn($partialShipment1, $partialShipment2);

        $zoneDetector = Mockery::mock(ZoneDetector::class);
        $zoneDetector->shouldReceive('getZoneForCustomerAddress')
                     ->once()
                     ->with($customerAddress)
                     ->andReturn($zone);

        $itemGrouper = Mockery::mock(PartialShipmentItemGrouperInterface::class);
        $itemGrouper->shouldReceive('group')
                    ->once()
                    ->with(Mockery::type('array'))
                    ->andReturnUsing(function (array $items) {
                        return collect($items)->mapToGroups(function (PartialShipmentItem $item) {
                            return [$item->getShippingCategory()->getId() => $item];
                        })->toArray();
                    });

        $validator = Mockery::mock(PartialShipmentValidator::class);
        $validator->shouldReceive('validate')
                  ->with(Mockery::type(ExpressPartialShipment::class), Mockery::type(\DateTimeInterface::class))
                  ->andReturn(new ConstraintViolationList([]));

        $partialShipmentService = new PartialShipmentService(
            $zoneDetector,
            $itemGrouper,
            $factory,
            $validator
        );

        $selectedValues = array_map(
            function ($id) {
                return (new CreateShipmentData())->setId($id)->setDeliveryDate(new \DateTime());
            },
            [$partialShipment1Id, $partialShipment2Id]
        );

        $isExpressDelivery = false;
        $freezedPartialShipments = $partialShipmentService->getPartialShipments(
            $cart,
            $customerAddress,
            $selectedValues,
            $isExpressDelivery
        );

        self::assertIsArray($freezedPartialShipments);

        foreach ($freezedPartialShipments as $freezedPartialShipment) {
            self::assertInstanceOf(BaseFreezedPartialShipment::class, $freezedPartialShipment);
        }
    }

    public function testItCreatePartialShipmentsFromInventory(): void
    {
        $customerAddress = Mockery::mock(CustomerAddress::class);

        $partialShipment = Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('calculate')
                        ->with(Mockery::type(\DateTimeInterface::class));

        $zone         = Mockery::mock(Zone::class);
        $zoneDetector = Mockery::mock(ZoneDetector::class);
        $zoneDetector->shouldReceive('getZoneForCustomerAddress')
                     ->once()
                     ->with($customerAddress)
                     ->andReturn($zone);

        $isExpressDelivery = false;

        $factory = Mockery::mock(PartialShipmentFactory::class);
        $factory->shouldReceive('create')
                ->with(
                    Mockery::type(\DateTimeInterface::class),
                    $zone,
                    Mockery::type('array'),
                    $isExpressDelivery
                )
                ->andReturn($partialShipment);

        $itemGrouper = Mockery::mock(PartialShipmentItemGrouperInterface::class);
        $itemGrouper->shouldReceive('group')
                    ->once()
                    ->with(Mockery::type('array'))
                    ->andReturnUsing(function (array $items) {
                        return [$items];
                    });

        $partialShipmentService = new PartialShipmentService(
            $zoneDetector,
            $itemGrouper,
            $factory,
            Mockery::mock(PartialShipmentValidator::class)
        );

        [$inventory] = $this->getInventories();

        $partialShipments  = $partialShipmentService->createFromInventory(
            $inventory,
            $customerAddress,
            $isExpressDelivery
        );

        self::assertIsArray($partialShipments);

        foreach ($partialShipments as $partialShipment) {
            self::assertInstanceOf(AbstractPartialShipment::class, $partialShipment);
        }
    }

    /**
     * @return array|Inventory[]
     */
    private function getInventories(): array
    {
        $inventories = [];

        foreach (range(1, 10) as $i) {
            $shippingCategory = Mockery::mock(ShippingCategory::class);
            $shippingCategory->shouldReceive('getId')->andReturn(random_int(1, 3));

            $product = new Product();
            $product->setShippingCategory($shippingCategory);

            $variant = new ProductVariant();
            $variant->setProduct($product);

            $inventory = new Inventory();
            $inventory->setVariant($variant)->setLeadTime(1)->setPrice(100000)->setFinalPrice(90000);

            $inventories[] = $inventory;
        }

        return $inventories;
    }
}
