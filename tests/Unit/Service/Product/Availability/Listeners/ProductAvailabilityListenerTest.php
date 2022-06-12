<?php

namespace App\Tests\Unit\Service\Product\Availability\Listeners;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Service\Product\Availability\Listeners\ProductAvailabilityListener;
use App\Service\Product\Availability\ProductAvailabilityChecker;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProductAvailabilityListenerTest extends BaseUnitTestCase
{
    public function testItDoNothingIfInventoryIsNotEligibleToChangeProductAvailability(): void
    {
        $bus                 = Mockery::mock(MessageBusInterface::class);
        $inventory           = Mockery::mock(Inventory::class);
        $availabilityChecker = Mockery::mock(ProductAvailabilityChecker::class);
        $availabilityChecker->shouldNotReceive('isAvailable');
        $availabilityChecker->shouldReceive(['inventoryIsEligibleToChangeProductAvailability' => false])
                            ->once()
                            ->with($inventory);

        $listener = new ProductAvailabilityListener($availabilityChecker, $bus);

        $listener->onInventoryPreFlush($inventory, Mockery::mock(PreFlushEventArgs::class));
    }

    public function testItDoNothingIfProductIsAvailableAndShouldBeAvailable(): void
    {
        $bus     = Mockery::mock(MessageBusInterface::class);
        $product = Mockery::mock(Product::class);

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getVariant->getProduct')->once()->withNoArgs()->andReturn($product);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldNotReceive('getUnitOfWork');

        $event = new PreFlushEventArgs($em);

        $methodNames = [
            'isAvailable'         => true,
            'shouldBeUnavailable' => false,
        ];

        $availabilityChecker = Mockery::mock(ProductAvailabilityChecker::class);
        $availabilityChecker->shouldReceive($methodNames)->once()->with($product);
        $availabilityChecker->shouldReceive('inventoryIsEligibleToChangeProductAvailability')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $listener = new ProductAvailabilityListener($availabilityChecker, $bus);

        $listener->onInventoryPreFlush($inventory, $event);
    }

    public function testItDoNothingIfProductIsUnavailableAndShouldBeUnavailable(): void
    {
        $bus     = Mockery::mock(MessageBusInterface::class);
        $product = Mockery::mock(Product::class);

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getVariant->getProduct')->once()->withNoArgs()->andReturn($product);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldNotReceive('getUnitOfWork');

        $event = new PreFlushEventArgs($em);

        $methods = [
            'isAvailable'       => false,
            'shouldBeAvailable' => false,
        ];

        $availabilityChecker = Mockery::mock(ProductAvailabilityChecker::class);
        $availabilityChecker->shouldReceive($methods)->once()->with($product);
        $availabilityChecker->shouldReceive('inventoryIsEligibleToChangeProductAvailability')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $listener = new ProductAvailabilityListener($availabilityChecker, $bus);

        $listener->onInventoryPreFlush($inventory, $event);
    }

    public function testItMakeProductUnavailable(): void
    {
        $bus = Mockery::mock(MessageBusInterface::class);

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('setStatus')->once()->with(ProductStatusDictionary::UNAVAILABLE)->andReturnSelf();
        $product->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getVariant->getProduct')->once()->withNoArgs()->andReturn($product);

        $classMetadata = Mockery::mock(ClassMetadata::class);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldNotReceive('getUnitOfWork->recomputeSingleEntityChangeSet')
           ->once()
           ->with($classMetadata, $product)
           ->andReturn();
        $em->shouldReceive('getClassMetadata')
           ->once()
           ->with(get_class($product))
           ->andReturn($classMetadata);

        $event = new PreFlushEventArgs($em);

        $methods = [
            'isAvailable'         => true,
            'shouldBeUnavailable' => true,
        ];

        $availabilityChecker = Mockery::mock(ProductAvailabilityChecker::class);
        $availabilityChecker->shouldReceive($methods)->once()->with($product);
        $availabilityChecker->shouldReceive('inventoryIsEligibleToChangeProductAvailability')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $listener = new ProductAvailabilityListener($availabilityChecker, $bus);

        $listener->onInventoryPreFlush($inventory, $event);
    }

    public function testItMakeProductAvailable(): void
    {
        $bus     = Mockery::mock(MessageBusInterface::class);
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('setStatus')->once()->with(ProductStatusDictionary::CONFIRMED)->andReturnSelf();
        $product->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getVariant->getProduct')->once()->withNoArgs()->andReturn($product);

        $classMetadata = Mockery::mock(ClassMetadata::class);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldNotReceive('getUnitOfWork->computeChangeSet')
           ->once()
           ->with($classMetadata, $product)
           ->andReturn();
        $em->shouldReceive('getClassMetadata')
           ->once()
           ->with(get_class($product))
           ->andReturn($classMetadata);

        $event = new PreFlushEventArgs($em);

        $methods = [
            'isAvailable'       => false,
            'shouldBeAvailable' => true,
        ];

        $availabilityChecker = Mockery::mock(ProductAvailabilityChecker::class);
        $availabilityChecker->shouldReceive($methods)->once()->with($product);
        $availabilityChecker->shouldReceive('inventoryIsEligibleToChangeProductAvailability')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $product->shouldReceive('getId')->once()->withNoArgs()->andReturn(10);

        $bus
            ->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AsyncMessage::class))
            ->andReturn(new Envelope(new stdClass()));

        $listener = new ProductAvailabilityListener($availabilityChecker, $bus);

        $listener->onInventoryPreFlush($inventory, $event);
    }
}
