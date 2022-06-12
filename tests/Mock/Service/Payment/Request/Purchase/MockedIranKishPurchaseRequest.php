<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\IrankishPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\IrankishPurchaseResponse;

/**
 * Class MockedIranKishPurchaseRequest
 */
class MockedIranKishPurchaseRequest extends IrankishPurchaseRequest
{
    /**
     * @param Transaction $transaction
     * @param string $callbackUrl
     *
     * @return AbstractPurchaseResponse
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['amount'] / 10 == 1234567890) {
            $status = false;
            $message = 'fail';
            $token = null;
        } else {
            $message = null;
            $token = '2979723';
            $status = true;
        }

        return new IrankishPurchaseResponse($status, $token, $message);
    }
}
