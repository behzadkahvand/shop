<?php

namespace App\Dictionary;

use App\Service\Notification\DTOs\Customer\Campaign\BlackFriday\BlackFridayLandingSmsNotificationDto;
use App\Service\Notification\DTOs\Customer\Campaign\Salam40\Salam40PromotionCodeSmsDTO;
use App\Service\Notification\DTOs\Customer\Order\AlertCustomerForPendingOrderSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Order\CustomerAbandonedCartSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Order\FirstTimeCallFailedSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Order\OrderCanceledSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Order\OrderRegisteredSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Order\SendOrderSurveySmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Payment\FailedPaymentSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Payment\RefundBalanceNotificationDTO;
use App\Service\Notification\DTOs\Customer\Product\NotifyAvailableSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\RateAndReview\RateAndReviewSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestApprovedNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsRefundedNotificationDTO;
use App\Service\Notification\DTOs\Customer\ReturnRequest\ReturnRequestIsWaitingRefundNotificationDTO;
use App\Service\Notification\DTOs\Customer\Shipping\ExpressSentShipmentSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Shipping\NonExpressSentShipmentSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSendShipmentSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSupplyShipmentSmsNotificationDTO;
use App\Service\Notification\DTOs\Customer\Wallet\WalletDepositNotificationDTO;
use App\Service\Notification\DTOs\Customer\Wallet\WalletWithdrawNotificationDTO;
use App\Service\Notification\DTOs\Seller\InventoryUpdateInitializedSmsNotificationDTO;
use App\Service\Notification\DTOs\Seller\NotifyDepotInventorySmsNotificationDTO;
use App\Service\Notification\DTOs\Seller\OnDemandInventoryIsOutOfStock;
use App\Service\Notification\DTOs\Seller\SellerDelayedOrderSmsNotificationDTO;
use App\Service\Notification\DTOs\Seller\SellerLandingSmsNotificationDTO;
use App\Service\Notification\DTOs\Seller\SellerPanelAccountSmsNotificationDTO;
use App\Service\Notification\DTOs\Seller\SellerPendingOrderSmsNotificationDTO;

class NotificationDTOClasseDictionary extends Dictionary
{
    public const ALL_NOTIFICATION_DTO = [
      BlackFridayLandingSmsNotificationDto::class,
      Salam40PromotionCodeSmsDTO::class,
      AlertCustomerForPendingOrderSmsNotificationDTO::class,
      CustomerAbandonedCartSmsNotificationDTO::class,
      FirstTimeCallFailedSmsNotificationDTO::class,
      OrderCanceledSmsNotificationDTO::class,
      OrderRegisteredSmsNotificationDTO::class,
      SendOrderSurveySmsNotificationDTO::class,
      FailedPaymentSmsNotificationDTO::class,
      RefundBalanceNotificationDTO::class,
      NotifyAvailableSmsNotificationDTO::class,
      RateAndReviewSmsNotificationDTO::class,
      ReturnRequestApprovedNotificationDTO::class,
      ReturnRequestIsRefundedNotificationDTO::class,
      ReturnRequestIsWaitingRefundNotificationDTO::class,
      ExpressSentShipmentSmsNotificationDTO::class,
      NonExpressSentShipmentSmsNotificationDTO::class,
      WaitingForSendShipmentSmsNotificationDTO::class,
      WaitingForSupplyShipmentSmsNotificationDTO::class,
      WalletDepositNotificationDTO::class,
      WalletWithdrawNotificationDTO::class,
      InventoryUpdateInitializedSmsNotificationDTO::class,
      NotifyDepotInventorySmsNotificationDTO::class,
      OnDemandInventoryIsOutOfStock::class,
      SellerDelayedOrderSmsNotificationDTO::class,
      SellerLandingSmsNotificationDTO::class,
      SellerPanelAccountSmsNotificationDTO::class,
      SellerPendingOrderSmsNotificationDTO::class
    ];
}
