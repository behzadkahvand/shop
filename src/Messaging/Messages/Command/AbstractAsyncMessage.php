<?php

namespace App\Messaging\Messages\Command;

abstract class AbstractAsyncMessage
{
    public function __construct(private object $message)
    {
    }

    public static function wrap(object $message): static
    {
        return new static($message);
    }

    public function getWrappedMessage(): object
    {
        return $this->message;
    }
}
