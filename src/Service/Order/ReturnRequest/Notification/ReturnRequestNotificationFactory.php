<?php

namespace App\Service\Order\ReturnRequest\Notification;

use App\Entity\ReturnRequest;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsRefundedNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsWaitingRefundNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestApprovedNotificationDTO;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestStatus;

class ReturnRequestNotificationFactory
{
    public function make(ReturnRequest $request): ?AbstractNotificationDTO
    {
        $customer = $request->getOrder()->getCustomer();

        return match ($request->getStatus()) {
            ReturnRequestStatus::APPROVED => new ReturnRequestApprovedNotificationDTO($customer),
            ReturnRequestStatus::WAITING_REFUND => new ReturnRequestIsWaitingRefundNotificationDTO($customer),
            ReturnRequestStatus::REFUNDED => new ReturnRequestIsRefundedNotificationDTO($customer),
            default => null,
        };
    }
}
