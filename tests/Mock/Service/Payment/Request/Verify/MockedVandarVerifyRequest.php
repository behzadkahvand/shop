<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\VandarVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\VandarVerifyResponse;

/**
 * Class MockedVandarVerifyRequest
 */
class MockedVandarVerifyRequest extends VandarVerifyRequest
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

        return new VandarVerifyResponse($status ? 0 : 1, $status ? 'error' : '');
    }
}
