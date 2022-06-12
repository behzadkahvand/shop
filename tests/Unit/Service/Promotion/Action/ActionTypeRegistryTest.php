<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Service\Promotion\Action\ActionTypeInterface;
use App\Service\Promotion\Action\ActionTypeRegistry;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ActionTypeRegistryTest extends MockeryTestCase
{
    private const ACTION_TYPE_ONE = 'action-type-one';

    private ServiceLocator|LegacyMockInterface|MockInterface|null $serviceLocator;

    private LegacyMockInterface|MockInterface|ActionTypeInterface|null $actionTypeOne;

    private ActionTypeRegistry $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceLocator = Mockery::mock(ServiceLocator::class);
        $this->actionTypeOne  = Mockery::mock(ActionTypeInterface::class);
        $this->serviceLocator
            ->shouldReceive('getProvidedServices')
            ->once()
            ->withNoArgs()
            ->andReturn([self::ACTION_TYPE_ONE => get_class($this->actionTypeOne)]);

        $this->sut = new ActionTypeRegistry($this->serviceLocator);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->serviceLocator = null;
        $this->actionTypeOne  = null;

        Mockery::close();
    }

    public function testGetNotExistingActionType(): void
    {
        $this->serviceLocator->shouldReceive('has')->once()->with('non_existing_action_type')->andReturnFalse();

        self::assertNull($this->sut->get('non_existing_action_type'));
    }

    public function testGetExistingActionType(): void
    {
        $this->serviceLocator->shouldReceive('has')->once()->with(self::ACTION_TYPE_ONE)->andReturnTrue();
        $this->serviceLocator->shouldReceive('get')->once()->with(self::ACTION_TYPE_ONE)->andReturn($this->actionTypeOne);

        self::assertEquals($this->actionTypeOne, $this->sut->get(self::ACTION_TYPE_ONE));
    }

    public function testGetActionTypeNames(): void
    {
        $actionTypeNames = $this->sut->getActionTypeNames();
        self::assertCount(1, $actionTypeNames);
        self::assertContains(self::ACTION_TYPE_ONE, $actionTypeNames);
    }
}
