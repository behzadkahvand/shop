<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\EventSubscriber\Order\ReturnRequest\WorkflowEvaluateSubscriber;
use App\Exceptions\Order\ReturnRequest\InvalidTransitionException;
use App\Repository\ReturnVerificationReasonRepository;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowEvaluateSubscriberTest extends BaseUnitTestCase
{
    private WorkflowEvaluateSubscriber|null $sut;
    private LegacyMockInterface|MockInterface|Event|null $event;
    private ReturnVerificationReasonRepository|LegacyMockInterface|MockInterface|null $verificationReasonRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Mockery::mock(Event::class);
        $this->verificationReasonRepository = Mockery::mock(ReturnVerificationReasonRepository::class);
        $this->sut = new WorkflowEvaluateSubscriber($this->verificationReasonRepository);
    }

    public function testTransitionShouldValidateThatWarehouseReasonExists(): void
    {
        $requestData = ['warehouseReasonId' => 1];
        $this->event->shouldReceive('getContext')->once()->withNoArgs()->andReturn($requestData);
        $this->verificationReasonRepository->shouldReceive('find')->once()->with(1)->andReturnNull();

        $this->expectException(InvalidTransitionException::class);
        $this->expectErrorMessage('Invalid warehouse reason.');

        $this->sut->onTransition($this->event);
    }

    public function testShouldReturnSubscribedEventsCorrectly(): void
    {
        $expected = [
            'workflow.return_request.transition.' . ReturnRequestTransition::WAREHOUSE_EVALUATE => 'onTransition',
        ];

        self::assertEquals($expected, $this->sut::getSubscribedEvents());
    }
}
