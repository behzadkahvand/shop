<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\ZibalPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\ZibalPurchaseResponse;

final class MockedZibalPurchaseRequest extends ZibalPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['amount'] / 10 == 1234567890) {
            $status  = 103;
            $token   = null;
            $payLink = '';
            $message = 'purchase failed';
        } else {
            $status  = 100;
            $token   = '084082';
            $payLink = 'test';
            $message = '';
        }

        return new ZibalPurchaseResponse($status, $token, $payLink, $message);
    }
}
