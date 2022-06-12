<?php

namespace App\Tests\Unit\Service\Order\OrderBalanceRefund;

use App\Entity\RefundDocument;
use App\Entity\Transaction;
use App\Service\Order\OrderBalanceRefund\OrderBalanceRefundFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderBalanceRefundFactoryTest extends MockeryTestCase
{
    protected OrderBalanceRefundFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new OrderBalanceRefundFactory();
    }

    protected function tearDown(): void
    {
        unset($this->factory);
    }

    public function testItCanGetRefundDocument()
    {
        $result = $this->factory->getRefundDocument();

        self::assertInstanceOf(RefundDocument::class, $result);
    }

    public function testItCanGetTransaction()
    {
        $result = $this->factory->getTransaction();

        self::assertInstanceOf(Transaction::class, $result);
    }
}
