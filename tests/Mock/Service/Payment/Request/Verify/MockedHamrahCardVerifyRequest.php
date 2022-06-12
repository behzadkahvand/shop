<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\HamrahCardVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\HamrahCardVerifyResponse;

class MockedHamrahCardVerifyRequest extends HamrahCardVerifyRequest
{
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        return new HamrahCardVerifyResponse($transaction->getAmount() == 1234567890 ? 1 : 0, 'test', $bankResponse->getToken());
    }
}
