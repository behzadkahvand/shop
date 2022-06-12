<?php

namespace App\Exceptions\Order;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvalidOrderStatusException extends Exception implements RenderableThrowableInterface
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected $message = 'Order status is invalid!';

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(false, $this->getCode(), $this->getMessage());
    }
}
