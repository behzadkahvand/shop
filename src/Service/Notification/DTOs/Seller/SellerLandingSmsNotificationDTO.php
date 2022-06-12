<?php

namespace App\Service\Notification\DTOs\Seller;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

/**
 * Class SellerLandingSmsNotificationDTO
 */
final class SellerLandingSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Recipient $recipient)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_LANDING;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::SELLER;
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
فروشنده عزیز، از پیشنهاد همکاری شما خرسندیم. 
کارشناسان ما به زودی با شما تماس خواهند گرفت .
ارتباط با تیمچه: 02191012959 
ایمیل: sellersupport@timcheh.com  
برای راهنمایی بیشتر و دریافت پاسخ سوالات خود به  پرسش های پر تکرار  مراجعه نمایید.
https://seller.timcheh.com/help
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key);

        return async_message(new SmsNotification(
            $this->recipient,
            $content,
            self::getCode()
        ));
    }
}
