<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\EFardaVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\EFardaVerifyResponse;

class MockedEFardaVerifyRequest extends EFardaVerifyRequest
{
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        $status = $transaction->getAmount() == 1234567890;

        return new EFardaVerifyResponse($status ? 300 : 0, $status ? 'success' : 'error');
    }
}
