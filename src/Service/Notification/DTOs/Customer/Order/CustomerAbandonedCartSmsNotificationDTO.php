<?php

namespace App\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

final class CustomerAbandonedCartSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Customer $customer)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_ABANDONED_CARD;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::CUSTOMER;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
ممکنه کالاهای موجود در سبد خریدت به‌زودی ناموجود بشن! پس همین حالا به سبد خریدت سر بزن و سفارشت رو نهایی کن!🛒
https://zaya.io/36kc4
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->customer),
                $content,
                self::getCode()
            )
        );
    }
}
