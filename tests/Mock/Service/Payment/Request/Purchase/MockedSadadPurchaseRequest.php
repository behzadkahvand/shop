<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\SadadPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\SadadPurchaseResponse;

final class MockedSadadPurchaseRequest extends SadadPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['Amount'] / 10 == 1234567890) {
            $status  = 1;
            $token   = null;
            $message = 'purchase failed';
        } else {
            $status  = 0;
            $token   = '084082';
            $message = '';
        }

        return new SadadPurchaseResponse($status, $token, $message);
    }
}
