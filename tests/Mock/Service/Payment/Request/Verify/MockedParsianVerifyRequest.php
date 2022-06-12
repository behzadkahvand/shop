<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\ParsianVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\ParsianVerifyResponse;

/**
 * Class MockedParsianVerifyRequest
 */
class MockedParsianVerifyRequest extends ParsianVerifyRequest
{
    /**
     * @param Transaction $transaction
     * @param AbstractBankResponse $bankResponse
     *
     * @return AbstractVerifyResponse
     */
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        $status = $transaction->getAmount() == 1234567890;

        return new ParsianVerifyResponse($status ? 1 : 0, $status ? -1 : '028408240', !$status);
    }
}
