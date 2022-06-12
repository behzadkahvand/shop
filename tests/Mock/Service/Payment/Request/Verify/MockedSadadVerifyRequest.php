<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\SadadVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\SadadVerifyResponse;

class MockedSadadVerifyRequest extends SadadVerifyRequest
{
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        $status = $transaction->getAmount() == 1234567890;

        return new SadadVerifyResponse($status ? 1 : 0, $status ? 'error' : '');
    }
}
