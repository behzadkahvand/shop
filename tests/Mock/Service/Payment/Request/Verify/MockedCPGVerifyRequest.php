<?php

namespace App\Tests\Mock\Service\Payment\Request\Verify;

use App\Entity\Transaction;
use App\Service\Payment\Request\Verify\CPGVerifyRequest;
use App\Service\Payment\Response\Bank\AbstractBankResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;
use App\Service\Payment\Response\Verify\CPGVerifyResponse;

/**
 * Class MockedCPGVerifyRequest
 */
class MockedCPGVerifyRequest extends CPGVerifyRequest
{
    /**
     * @param Transaction $transaction
     * @param AbstractBankResponse $bankResponse
     *
     * @return AbstractVerifyResponse
     */
    public function send(Transaction $transaction, AbstractBankResponse $bankResponse): AbstractVerifyResponse
    {
        $response = new CPGVerifyResponse($transaction->getAmount() == 1234567890 ? 0 : 1, 'test', '');

        $bankResponse->setReferenceNumber($response->getTransId());
        $bankResponse->setTrackingNumber($response->getTransId());

        return $response;
    }
}
