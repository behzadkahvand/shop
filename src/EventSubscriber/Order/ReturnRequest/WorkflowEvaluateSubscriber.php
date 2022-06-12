<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Exceptions\Order\ReturnRequest\InvalidTransitionException;
use App\Repository\ReturnVerificationReasonRepository;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowEvaluateSubscriber implements EventSubscriberInterface
{
    public function __construct(protected ReturnVerificationReasonRepository $verificationReasonRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.return_request.transition.' . ReturnRequestTransition::WAREHOUSE_EVALUATE => 'onTransition',
        ];
    }

    /**
     * @throws InvalidTransitionException
     */
    public function onTransition(Event $event): void
    {
        $this->validateRequestData($event);
    }

    /**
     * @throws InvalidTransitionException
     */
    private function validateRequestData(Event $event): void
    {
        $requestData = $event->getContext();

        if (null === $this->verificationReasonRepository->find($requestData['warehouseReasonId'])) {
            throw new InvalidTransitionException('Invalid warehouse reason.');
        }
    }
}
