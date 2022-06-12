<?php

namespace App\Events\OTP;

use App\Entity\Customer;
use App\Response\Auth\OtpVerifyResponse;
use Symfony\Contracts\EventDispatcher\Event;

class OtpLoginEvent extends Event
{
    protected Customer $user;

    protected OtpVerifyResponse $response;

    public function __construct(Customer $user, OtpVerifyResponse $response)
    {
        $this->user = $user;
        $this->response = $response;
    }

    public function getUser(): Customer
    {
        return $this->user;
    }

    public function getResponse(): OtpVerifyResponse
    {
        return $this->response;
    }
}
