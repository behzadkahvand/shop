<?php

namespace App\Service\Notification\DTOs;

use App\Service\Notification\RecipientFactory;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractNotificationDTO
{
    abstract public static function getCode(): string;

    abstract public static function getSection(): string;

    abstract public static function getNotificationType(): string;

    abstract public static function getVariablesDescription(): array;

    abstract public static function getDefaultTemplate(): string;

    abstract public function getMessage(Environment $templateEngine, string $key): object;

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render(Environment $templateEngine, string $key, array $parameters = []): string
    {
        return $templateEngine->render($key, $parameters);
    }

    protected function makeRecipientFactory(): RecipientFactory
    {
        return new RecipientFactory();
    }
}
