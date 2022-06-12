<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Entity\ReturnRequestItem;
use App\Events\Order\ReturnRequest\ReturnRequestStatusUpdated;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowAnnounceSubscriber implements EventSubscriberInterface
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.return_request.announce' => 'onAfterTransition',
        ];
    }

    public function onAfterTransition(Event $event): void
    {
        /** @var ReturnRequestItem $returnRequestItem*/
        $returnRequestItem = $event->getSubject();
        $returnRequest = $returnRequestItem->getRequest();
        $statusHasChanged = $returnRequest->updateStatus();

        if ($statusHasChanged) {
            $this->dispatcher->dispatch(new ReturnRequestStatusUpdated($returnRequest));
        }
    }
}
