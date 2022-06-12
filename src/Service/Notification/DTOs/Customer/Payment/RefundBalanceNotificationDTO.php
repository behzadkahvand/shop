<?php

namespace App\Service\Notification\DTOs\Customer\Payment;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Order;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

final class RefundBalanceNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Order $order, private int $amount)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_BALANCE_REFUND;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::ORDER;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'amount'          => 'Refund amount',
            'orderIdentifier' => 'Order identifier',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
مشتری عزیز تیمچه
مبلغ {{amount}} تومان بابت سفارش  {{orderIdentifier}} به حساب شما واریز شد.
امیدواریم برای سفارش های بعدی در خدمت شما باشیم.
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'orderIdentifier' => $this->order->getIdentifier(),
            'amount'          => $this->amount,
        ]);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->order->getCustomer()),
                $content,
                self::getCode()
            )
        );
    }
}
