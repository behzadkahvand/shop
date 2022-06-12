<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\EFardaPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\EFardaPurchaseResponse;

final class MockedEFardaPurchaseRequest extends EFardaPurchaseRequest
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
            $doTime = '';
            $message = 'purchase failed';
        } else {
            $status  = 0;
            $token   = '084082';
            $doTime = new \DateTime();
            $message = '';
        }

        return new EFardaPurchaseResponse($status, $token, $doTime, $message);
    }
}
