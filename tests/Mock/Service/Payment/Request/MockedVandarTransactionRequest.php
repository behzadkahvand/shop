<?php

namespace App\Tests\Mock\Service\Payment\Request;

use App\Entity\Transaction;
use App\Service\Payment\Request\Transaction\VandarTransactionRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\VandarTransactionResponse;

/**
 * Class MockedVandarTransactionRequest
 */
class MockedVandarTransactionRequest extends VandarTransactionRequest
{
    /**
     * @param Transaction $transaction
     * @param AbstractBankResponse $bankResponse
     *
     * @return VandarTransactionResponse
     */
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): VandarTransactionResponse
    {
        return new VandarTransactionResponse(12345678, 12345678);
    }
}
