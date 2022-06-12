<?php

namespace App\Tests\Mock\Service\Payment\Request\Purchase;

use App\Entity\Transaction;
use App\Service\Payment\Request\Purchase\HamrahCardPurchaseRequest;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Purchase\HamrahCardPurchaseResponse;

final class MockedHamrahCardPurchaseRequest extends HamrahCardPurchaseRequest
{
    /**
     * @inheritDoc
     */
    public function send(Transaction $transaction, string $callbackUrl): AbstractPurchaseResponse
    {
        $data = parent::preparePurchaseData($transaction, $callbackUrl);

        if ($data['amount'] / 10 == 1234567890) {
            $status  = 2;
            $token   = null;
            $message = 'purchase failed';
            $‫‪qrCodeValue‬‬ = null;
            $doTime      = null;
            $deeplink    = null;
        } else {
            $status      = 0;
            $token       = '084082';
            $message     = '';
            $‫‪qrCodeValue‬‬ = 'qr code value';
            $doTime      = '1399/12/17';
            $deeplink    = 'timcheh.local';
        }

        return new HamrahCardPurchaseResponse($status, $token, $‫‪qrCodeValue‬‬, $doTime, $deeplink, $message);
    }
}
