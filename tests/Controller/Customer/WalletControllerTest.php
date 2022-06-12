<?php

namespace App\Tests\Controller\Customer;

use App\DTO\Wallet\TransferRequest;
use App\Entity\Wallet;
use App\Tests\Controller\BaseControllerTestCase;

class WalletControllerTest extends BaseControllerTestCase
{
    public function testWalletHistories(): void
    {
        $wallet = new Wallet();
        $this->customer->setWallet($wallet);
        $wallet->deposit($this->makeTransferRequest(100));
        $wallet->withdraw($this->makeTransferRequest(50));
        $wallet->deposit($this->makeTransferRequest(500));
        $this->manager->flush();

        $client = $this->loginAs($this->customer)->sendRequest(
            'GET',
            sprintf('/wallet/histories'),
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSuccessResponseKeys();

        $response = $this->getControllerResponse();

        self::assertCount(3, $response['results']);

        $histories = $response['results'];
        self::assertNotEmpty($histories);
        self::assertArrayHasKeys(
            [
                "amount",
                "type",
                "order",
                "referenceId",
                "reason",
                "beforeAmount",
                "afterAmount",
                "createdAt"
            ],
            $histories[0]
        );
    }

    private function makeTransferRequest(int $amout): TransferRequest
    {
        return new TransferRequest(
            $amout,
            'dummy resson'
        );
    }
}
