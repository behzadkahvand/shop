<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Entity\ReturnRequest;
use App\Events\Order\ReturnRequest\ReturnRequestRegistered;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Registry;

class AutomaticTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(private Registry $registry)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ReturnRequestRegistered::class => 'onReturnRequestRegistered'
        ];
    }

    public function onReturnRequestRegistered(ReturnRequestRegistered $event): void
    {
        $returnRequest = $event->getReturnRequest();

        $this->applyWaitForRefundTransitionForNonReturnableItems($returnRequest);
    }

    private function applyWaitForRefundTransitionForNonReturnableItems(ReturnRequest $returnRequest): void
    {
        foreach ($returnRequest->getItems() as $item) {
            if ($item->isApproved() && !$item->getIsReturnable()) {
                $workflow = $this->registry->get($item);
                $workflow->apply($item, ReturnRequestTransition::WAIT_FOR_REFUND);
            }
        }
    }
}
