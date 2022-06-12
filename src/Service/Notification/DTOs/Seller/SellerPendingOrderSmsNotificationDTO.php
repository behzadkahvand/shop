<?php

namespace App\Service\Notification\DTOs\Seller;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Seller;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

/**
 * Class SellerPendingOrderSmsNotificationDTO
 */
final class SellerPendingOrderSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Seller $seller)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_PENDING_ORDER;
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
فروشنده عزیز،
بسیار خوشحالیم که سفارشات جدید در پنل خود دارید
هم اکنون برای ارسال کالای خود وارد پنل شده و در قسمت سفارشات، مراحل ثبت محموله را بررسی نمائید.
ارتباط با تیمچه: 02191012959 
داخلی: 431 – 432 – 433 - 434
ایمیل: sellersupport@timcheh.com  
برای راهنمایی بیشتر و دریافت پاسخ سوالات خود به  پرسش های پر تکرار  مراجعه نمایید.
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->seller),
                $content,
                self::getCode()
            )
        );
    }
}
