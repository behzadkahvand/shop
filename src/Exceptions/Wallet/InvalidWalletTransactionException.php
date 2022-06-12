<?php

namespace App\Exceptions\Wallet;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvalidWalletTransactionException extends Exception implements RenderableThrowableInterface
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, $this->getCode(), $this->getMessage());
    }
}
