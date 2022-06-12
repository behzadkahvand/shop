<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\CPGPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\CPGPurchaseResponse;

/**
 * Class MockedCPGPurchaseRequest
 */
final class MockedCPGPurchaseRequest extends CPGPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['amount'] / 10 == 1234567890) {
            $status = 0;
            $token  = null;
        } else {
            $status = 1;
            $token  = '084082';
        }

        return new CPGPurchaseResponse($status, $token, '');
    }
}
