<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Service\Order\Stages\RemoveCustomerCartStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

final class RemoveCustomerCartStageTest extends MockeryTestCase
{
    public function testItCanRemoveCustomerCart(): void
    {
        $customer = m::mock(Customer::class);
        $customer->shouldReceive('setCart')->once()->with(null)->andReturnSelf();

        $cart = m::mock(Cart::class);
        $cart->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($customer);
        $cart->shouldReceive('setCustomer')->once()->with(null)->andReturnSelf();

        $manager = m::spy(EntityManagerInterface::class);

        $payload = m::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getCart')->once()->withNoArgs()->andReturn($cart);
        $payload->shouldReceive('getEntityManager')->once()->withNoArgs()->andReturn($manager);

        $stage = new RemoveCustomerCartStage();

        self::assertSame($payload, $stage($payload));
        $manager->shouldHaveReceived('remove')->once()->with($cart);
    }
}
