<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Exceptions\Order\ReturnRequest\InvalidTransitionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Exception\InvalidDefinitionException;

class WorkflowTransitionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.return_request.transition' => 'onTransition',
        ];
    }

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
        $requiredData = $event->getMetadata('requiredData', $event->getTransition());

        if (null === $requiredData) {
            $requiredData = [];
        }

        if (!is_array($requiredData)) {
            throw new InvalidDefinitionException('requiredData in workflow definition must be an array.');
        }

        $this->validateAllRequiredDataAreProvided($requiredData, $requestData);

        $this->validateNoExtraDataIsProvided($requestData, $requiredData);
    }

    /**
     * @throws InvalidTransitionException
     */
    private function validateAllRequiredDataAreProvided(
        array $requiredData,
        array $requestData
    ): void {
        foreach ($requiredData as $requireInput) {
            if (!array_key_exists($requireInput, $requestData)) {
                throw new InvalidTransitionException(
                    'Required data missing: ' . $requireInput
                );
            }
        }
    }

    /**
     * @throws InvalidTransitionException
     */
    private function validateNoExtraDataIsProvided(
        array $requestData,
        array $requiredData
    ): void {
        foreach ($requestData as $key => $value) {
            if (!in_array($key, $requiredData)) {
                throw new InvalidTransitionException(
                    'Invalid data provided: ' . $key
                );
            }
        }
    }
}
