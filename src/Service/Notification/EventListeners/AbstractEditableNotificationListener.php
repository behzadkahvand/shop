<?php

namespace App\Service\Notification\EventListeners;

use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEditableNotificationListener implements EventSubscriberInterface
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        $events = static::getEvents();
        $methods = array_pad([], count($events), '__invoke');

        return array_combine($events, $methods);
    }

    final public function __invoke(Event $event): void
    {
        if ($this->shouldBail($event)) {
            return;
        }

        $this->notificationService->send($this->getDTO($event));
    }

    abstract protected static function getEvents(): array;

    abstract protected function getDTO(Event $event): AbstractNotificationDTO;

    abstract protected function shouldBail(Event $event): bool;
}
