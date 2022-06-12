<?php

namespace App\Service\Notification\DTOs\Customer\Campaign\BlackFriday;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

class BlackFridayLandingSmsNotificationDto extends AbstractNotificationDTO
{
    public function __construct(private string $mobile)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_BLACK_FRIDAY_CAMPAIGN;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::CAMPAIGN;
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
دوست خوب تیمچه شماره‌ت با موفقیت ثبت شد.
ما از شروع بلک فرایدی تیمچه با خبرت می‌کنیم.
تا ۹۹ درصد تخفیف در «این جمعه وقتشه»
راستی به اینستاگرام تیمچه هم سر بزن، کلی تخفیف جذاب داریم:
https://instagram.com/timchehcom?utm_medium=copy_link
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        return async_message(
            new SmsNotification(
                new Recipient($this->mobile),
                $this->render($templateEngine, $key),
                self::getCode()
            )
        );
    }
}
