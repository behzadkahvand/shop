<?php

namespace App\Tests\Mock\Service\Payment\Gateway;

use App\Entity\Transaction;
use App\Service\Payment\Gateways\AbstractGateway;
use App\Service\Payment\PurchaseData\HttpPostPurchaseResponse;
use App\Service\Payment\PurchaseData\HttpPurchaseResponse;
use App\Service\Payment\Response\Purchase\AbstractPurchaseResponse;
use App\Service\Payment\Response\Verify\AbstractVerifyResponse;

/**
 * Class MockedAbstractGateway
 */
final class MockedAbstractGateway extends AbstractGateway
{
    /**
     * @inheritDoc
     */
    protected function preparePurchaseResponse(
        AbstractPurchaseResponse $gatewayResponse,
        Transaction $transaction,
        string $callbackUrl
    ): HttpPurchaseResponse {
        return new HttpPostPurchaseResponse($callbackUrl, []);
    }

    /**
     * @inheritDoc
     */
    protected function getFailedPaymentExceptionMessage(AbstractVerifyResponse $verifyResponse): ?string
    {
        return null;
    }

    public static function getName(): string
    {
    }

    public static function getCategory(): string
    {
    }

    public function getDescription(): string
    {
    }
}
