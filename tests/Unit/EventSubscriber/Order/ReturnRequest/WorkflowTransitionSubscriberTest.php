<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\EventSubscriber\Order\ReturnRequest\WorkflowTransitionSubscriber;
use App\Exceptions\Order\ReturnRequest\InvalidTransitionException;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Exception\InvalidDefinitionException;
use Symfony\Component\Workflow\Transition;

class WorkflowTransitionSubscriberTest extends BaseUnitTestCase
{
    private WorkflowTransitionSubscriber|null $sut;
    private LegacyMockInterface|MockInterface|Event|null $event;
    private Transition|LegacyMockInterface|MockInterface|null $transition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transition = Mockery::mock(Transition::class);
        $this->event = Mockery::mock(Event::class);
        $this->sut = new WorkflowTransitionSubscriber();
    }

    public function testShouldValidateThatAllRequiredDataAreProvidedInRequest(): void
    {
        $requestData = ['input_1' => 'some value'];
        $requiredData = ['input_1', 'input_2'];
        $this->event->shouldReceive('getContext')->once()->withNoArgs()->andReturn($requestData);
        $this->event->shouldReceive('getMetadata')->once()->with('requiredData', $this->transition)->andReturn($requiredData);
        $this->event->shouldReceive('getTransition')->once()->withNoArgs()->andReturn($this->transition);

        $this->expectException(InvalidTransitionException::class);
        $this->expectErrorMessage('Required data missing: input_2');

        $this->sut->onTransition($this->event);
    }

    public function testShouldValidateThatNoExtraDataIsProvidedInRequest(): void
    {
        $requestData = ['input_1' => 'some value', 'input_2' => 'some value', 'invalid_input' => 'some value'];
        $requiredData = ['input_1', 'input_2'];
        $this->event->shouldReceive('getContext')->once()->withNoArgs()->andReturn($requestData);
        $this->event->shouldReceive('getMetadata')->once()->with('requiredData', $this->transition)->andReturn($requiredData);
        $this->event->shouldReceive('getTransition')->once()->withNoArgs()->andReturn($this->transition);

        $this->expectException(InvalidTransitionException::class);
        $this->expectErrorMessage('Invalid data provided: invalid_input');

        $this->sut->onTransition($this->event);
    }

    public function testShouldValidateThatRequiredDataIsOfTypeArray(): void
    {
        $requestData = ['input_1' => 'some value', 'input_2' => 'some value'];
        $requiredData = 'some string';
        $this->event->shouldReceive('getContext')->once()->withNoArgs()->andReturn($requestData);
        $this->event->shouldReceive('getMetadata')->once()->with('requiredData', $this->transition)->andReturn($requiredData);
        $this->event->shouldReceive('getTransition')->once()->withNoArgs()->andReturn($this->transition);

        $this->expectException(InvalidDefinitionException::class);
        $this->expectErrorMessage('requiredData in workflow definition must be an array.');

        $this->sut->onTransition($this->event);
    }

    public function testShouldReturnSubscribedEventsCorrectly(): void
    {
        $expected = [
            'workflow.return_request.transition' => 'onTransition',
        ];

        self::assertEquals($expected, $this->sut::getSubscribedEvents());
    }
}
