<?php

namespace App\Messaging\Messages\Command;

class AsyncMessageFactory
{
    public function create(object $message): AsyncMessage
    {
        return AsyncMessage::wrap($message);
    }
}
