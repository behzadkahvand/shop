<?php

namespace App\Service\Notification\DTOs\Customer\Campaign\Salam40;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

/**
 * Class Salam40PromotionCodeSmsDTO
 */
final class Salam40PromotionCodeSmsDTO extends AbstractNotificationDTO
{
    private string $mobile;

    /**
     * Salam40PromotionCodeSmsDTO constructor.
     *
     * @param string $mobile
     */
    public function __construct(string $mobile)
    {
        $this->mobile = $mobile;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_SALAM_40_CAMPAIGN;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::CAMPAIGN;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
کد تخفیف ۴۰هزار تومنی، عیدی تیمچه به شما:
ma66re2
خرید با ارسال رایگان:
timcheh.com
برای اولین خرید و سفارش‌ بالای ۱۰۰هزار تومن
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

    public static function getVariablesDescription(): array
    {
        return [];
    }
}
