<?php

namespace App\Tests\Mock\Service\Payment\Request\Reverse;

use App\Service\Payment\Request\Reverse\MellatReversalRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;

/**
 * Class MockedMellatTransactionRequest
 */
class MockedMellatReversalRequest extends MellatReversalRequest
{
    /**
     * @param AbstractBankResponse $bankResponse
     *
     * @return void
     */
    public function send(AbstractBankResponse $bankResponse): void
    {
        //todo: make response for fail and success tests
    }
}
