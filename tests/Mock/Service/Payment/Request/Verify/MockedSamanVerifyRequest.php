<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\SamanVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\SamanVerifyResponse;

/**
 * Class MockedSamanVerifyRequest
 */
class MockedSamanVerifyRequest extends SamanVerifyRequest
{
    /**
     * @param Transaction $transaction
     * @param AbstractBankResponse $bankResponse
     *
     * @return AbstractVerifyResponse
     */
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        return new SamanVerifyResponse($transaction->getAmount() == 1234567890 ? -2 : 1);
    }
}
