<?php

namespace App\Events\Order\ReturnRequest;

use App\Entity\ReturnRequest;
use Symfony\Contracts\EventDispatcher\Event;

class ReturnRequestRegistered extends Event
{
    public function __construct(private ReturnRequest $returnRequest)
    {
    }

    public function getReturnRequest(): ReturnRequest
    {
        return $this->returnRequest;
    }
}
