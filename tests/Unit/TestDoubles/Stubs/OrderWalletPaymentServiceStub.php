<?php

namespace App\Tests\Unit\TestDoubles\Stubs;

use App\Entity\RefundDocument;
use App\Service\Notification\NotificationService;
use App\Service\Order\Wallet\OrderWalletPaymentService;
use App\Service\Payment\TransactionIdentifierService;
use Doctrine\ORM\EntityManagerInterface;

class OrderWalletPaymentServiceStub extends OrderWalletPaymentService
{
    public function __construct(
        protected EntityManagerInterface $manager,
        protected TransactionIdentifierService $identifierService,
        protected RefundDocument $refundDocument,
        protected NotificationService $notificationService
    ) {
        parent::__construct($manager, $identifierService, $notificationService);
    }

    protected function createRefundDocument(): RefundDocument
    {
        return $this->refundDocument;
    }
}
