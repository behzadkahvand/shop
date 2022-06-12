<?php

namespace App\Tests\Mock\Service\Payment\Request;

use App\Service\Payment\Request\Transaction\MellatTransactionRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\MellatTransactionResponse;

/**
 * Class MockedMellatTransactionRequest
 */
class MockedMellatTransactionRequest extends MellatTransactionRequest
{
    /**
     * @param AbstractBankResponse $bankResponse
     *
     * @return MellatTransactionResponse
     */
    public function send(AbstractBankResponse $bankResponse): MellatTransactionResponse
    {
        return new MellatTransactionResponse((int) empty($bankResponse->getSaleReferenceId()), !empty($bankResponse->getSaleReferenceId()));
    }
}
