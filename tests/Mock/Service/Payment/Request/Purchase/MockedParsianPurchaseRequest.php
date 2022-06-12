<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\ParsianPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\ParsianPurchaseResponse;

/**
 * Class MockedParsianPurchaseRequest
 */
final class MockedParsianPurchaseRequest extends ParsianPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['Amount'] / 10 == 1234567890) {
            $status  = 1;
            $message = 'fail';
            $token   = null;
        } else {
            $status  = 0;
            $message = null;
            $token   = 9823480842;
        }

        return new ParsianPurchaseResponse($status, $token, $message);
    }
}
