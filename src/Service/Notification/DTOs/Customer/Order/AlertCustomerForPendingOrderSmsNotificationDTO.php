<?php

namespace App\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Order;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

final class AlertCustomerForPendingOrderSmsNotificationDTO extends AbstractNotificationDTO
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_PENDING_ORDER;
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
            'name'            => 'Customer first name',
            'orderIdentifier' => 'Order identifier',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{name}} عزیز
متاسفانه پرداخت شما ناموفق بود. ما اقلام سفارش شما به شماره {{orderIdentifier}} رو توی سبد خریدتون رزرو می‌کنیم.
پس لطفا ظرف 30 دقیقه آینده به حساب کاربری خود سر بزنید و پرداخت رو انجام بدید.

https://timcheh.com/dashboard/profile
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'orderIdentifier' => $this->order->getIdentifier(),
            'name'            => $this->order->getCustomer()->getName(),
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
