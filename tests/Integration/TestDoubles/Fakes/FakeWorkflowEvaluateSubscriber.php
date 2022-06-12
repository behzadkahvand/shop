<?php

namespace App\Tests\Integration\TestDoubles\Fakes;

use App\EventSubscriber\Order\ReturnRequest\WorkflowTransitionSubscriber;
use Symfony\Component\Workflow\Event\Event;

class FakeWorkflowEvaluateSubscriber extends WorkflowTransitionSubscriber
{
    public function onTransition(Event $event): void
    {
        return;
    }
}
