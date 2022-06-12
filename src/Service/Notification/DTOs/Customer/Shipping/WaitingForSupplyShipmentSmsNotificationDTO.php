<?php

namespace App\Service\Notification\DTOs\Customer\Shipping;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

final class WaitingForSupplyShipmentSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(
        private string $customerName,
        private string $customerMobile,
        private string $orderIdentifier,
        private string $customerFullName,
        private int $customerId
    ) {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_WAITING_FOR_SUPPLY_SHIPMENT;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::ORDER_SHIPMENT;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'name'      => 'Customer first name',
            'orderCode' => 'Order identifier',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{name}} عزیز
بابت تاخیر در ارسال سفارشتون به شماره {{orderCode}} عمیقا متاسفیم.
همکاران ما نهایتا طی 48 ساعت آینده با شما در ارتباط خواهند بود.
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'name'      => $this->customerName,
            'orderCode' => $this->orderIdentifier,
        ]);

        $recipient = new Recipient(
            $this->customerMobile,
            $this->customerFullName,
            Customer::class,
            $this->customerId
        );

        return async_message(new SmsNotification(
            $recipient,
            $content,
            self::getCode()
        ));
    }
}
