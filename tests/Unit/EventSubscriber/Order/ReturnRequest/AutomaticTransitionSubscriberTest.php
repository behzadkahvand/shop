<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Events\Order\ReturnRequest\ReturnRequestRegistered;
use App\EventSubscriber\Order\ReturnRequest\AutomaticTransitionSubscriber;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class AutomaticTransitionSubscriberTest extends BaseUnitTestCase
{
    private LegacyMockInterface|ReturnRequestRegistered|MockInterface|null $event;
    private ReturnRequest|null $returnRequest;
    private ReturnRequestItem|null $item_1;
    private ReturnRequestItem|null $item_2;
    private LegacyMockInterface|MockInterface|Registry|null $registry;
    private LegacyMockInterface|Workflow|MockInterface|null $workflow;
    private AutomaticTransitionSubscriber|null $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->returnRequest = new ReturnRequest();
        $this->item_1 = new ReturnRequestItem();
        $this->item_2 = new ReturnRequestItem();
        $this->registry = Mockery::mock(Registry::class);
        $this->workflow = Mockery::mock(Workflow::class);
        $this->event = Mockery::mock(ReturnRequestRegistered::class);
        $this->returnRequest->addItem($this->item_1);
        $this->returnRequest->addItem($this->item_2);

        $this->sut = new AutomaticTransitionSubscriber($this->registry);
    }

    public function testOnRegisteredShouldApplyWaitForRefundTransitionForNonReturnableItems(): void
    {
        $this->item_1->setIsReturnable(true);
        $this->item_2->setIsReturnable(false);

        $this->registry->shouldReceive('get')->once()->with($this->item_2)->andReturn($this->workflow);
        $this->workflow
            ->shouldReceive('apply')
            ->once()
            ->with($this->item_2, ReturnRequestTransition::WAIT_FOR_REFUND);

        $this->event->shouldReceive('getReturnRequest')->once()->withNoArgs()->andReturn($this->returnRequest);

        $this->sut->onReturnRequestRegistered($this->event);
    }

    public function testShouldReturnSubscribedEventsCorrectly(): void
    {
        $expected = [
            ReturnRequestRegistered::class => 'onReturnRequestRegistered'
        ];

        self::assertEquals($expected, $this->sut::getSubscribedEvents());
    }
}
