<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase\COD;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\COD\ZibalPurchaseRequest;
use App\Service\Payment\Response\Purchase\COD\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\COD\ZibalPurchaseResponse;

final class MockedZibalPurchaseRequest extends ZibalPurchaseRequest
{
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        return new ZibalPurchaseResponse(
            '727',
            10000,
            1,
            'success',
        );
    }
}
