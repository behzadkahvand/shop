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
 * Class SellerPanelAccountSmsNotificationDTO
 */
final class SellerPanelAccountSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Seller $seller)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_PANEL_ACCOUNT;
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
هم اکنون، دسترسی شما برای فعالیت در پنل فروشندگان تیمچه کامل شده است ، شما می توانید از طریق لینک زیر با وارد کردن نام کاربری و کلمه عبور ، اقدام به درج و قیمت گذاری کالاهای خود نمائید.
ارتباط با تیمچه:   02191012959 
ایمیل: sellersupport@timcheh.com  
لینک پنل فروشندگان تیمچه : https://seller.timcheh.com/auth/login
نام کاربری : ایمیل شما
رمز عبور :کد ملی / شناسه ملی
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
