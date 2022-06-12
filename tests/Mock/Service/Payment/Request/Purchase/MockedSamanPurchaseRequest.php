<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\SamanPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\SamanPurchaseResponse;

/**
 * Class MockedSamanPurchaseRequest
 */
final class MockedSamanPurchaseRequest extends SamanPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        if ($transaction->getDocument()->getAmount() == 1234567890) {
            $status = -1;
            $token  = null;
        } else {
            $status = null;
            $token  = '0892408042';
        }

        return new SamanPurchaseResponse($status, $token);
    }
}
