<?php

namespace App\Service\Notification\EventListeners;

/**
 * Interface EditableNotificationListenerInterface
 */
interface EditableNotificationListenerInterface
{
    /**
     * @return string
     */
    public static function getCode(): string;

    /**
     * @return string
     */
    public static function getSection(): string;

    /**
     * @return string
     */
    public static function getNotificationType(): string;

    /**
     * @return array
     */
    public static function getVariablesDescription(): array;
}
