<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\MellatPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\MellatPurchaseResponse;

/**
 * Class MockedMellatPurchaseRequest
 */
class MockedMellatPurchaseRequest extends MellatPurchaseRequest
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
            $status = 1;
            $message = 'fail';
            $token = '';
        } else {
            $message = null;
            $token = '2979723';
            $status = 0;
        }

        return new MellatPurchaseResponse($status, $token, $message);
    }
}
