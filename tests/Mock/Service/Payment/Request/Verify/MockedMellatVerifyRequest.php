<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Transaction\MellatTransactionRequest;
use App\Service\Payment\Request\Verify\IrankishVerifyRequest;
use App\Service\Payment\Request\Verify\MellatVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\IranKishVerifyResponse;
use App\Service\Payment\Response\Verify\MellatVerifyResponse;

/**
 * Class MockedMellatVerifyRequest
 */
class MockedMellatVerifyRequest extends MellatVerifyRequest
{
    /**
     * @param Transaction $transaction
     * @param AbstractBankResponse $bankResponse
     *
     * @return MellatVerifyResponse
     */
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): MellatVerifyResponse
    {
        $status = $transaction->getAmount() == 1234567890;

        return new MellatVerifyResponse($status ? 1 : 0, !$status);
    }
}
