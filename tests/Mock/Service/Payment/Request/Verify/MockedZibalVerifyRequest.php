<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\ZibalVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\ZibalVerifyResponse;

class MockedZibalVerifyRequest extends ZibalVerifyRequest
{
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        $status = $transaction->getAmount() == 1234567890;

        return new ZibalVerifyResponse($status ? 0 : 100, $status ? 'error' : 'success');
    }
}
