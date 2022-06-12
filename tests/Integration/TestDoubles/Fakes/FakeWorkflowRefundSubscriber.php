<?php

namespace App\Tests\Integration\TestDoubles\Fakes;

use App\EventSubscriber\Order\ReturnRequest\WorkflowRefundSubscriber;
use Symfony\Component\Workflow\Event\Event;

class FakeWorkflowRefundSubscriber extends WorkflowRefundSubscriber
{
    public function onRefundComplete(Event $event): void
    {
    }
}
