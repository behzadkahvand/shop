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
 * Class SellerDelayedOrderSmsNotificationDTO
 */
final class SellerDelayedOrderSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Seller $seller)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_DELAYED_ORDER;
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
آیتم های سفارشات شما، دچار تاخیر در ارسال و تحویل به انبار شده است، لطفا نسبت به ارسال سریعتر محصولات خود اقدام کنید.
ارتباط با تیمچه: 02191012959 
داخلی: 432 – 433 - 434
ایمیل: sellersupport@timcheh.com
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
