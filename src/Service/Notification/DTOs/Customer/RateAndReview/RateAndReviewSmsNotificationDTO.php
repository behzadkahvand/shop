<?php

namespace App\Service\Notification\DTOs\Customer\RateAndReview;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

/**
 * Class RateAndReviewSmsNotificationDTO
 */
final class RateAndReviewSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(private Customer $customer, private string $url)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_RATE_AND_REVIEW;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::PRODUCT_RATE_AND_REVIEW;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'name' => 'Customer first name',
            'url'  => 'Link to submit a product rate and review',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{name}} عزیز
از شما دعوت می‌کنیم نظرتون رو در مورد کالاهایی که از تیمچه خریدید، ثبت کنید.
پیشاپیش از اینکه با ثبت نظر و انتقال تجریه خودتون به انتخاب بقیه کمک می‌کنید ممنونیم.
جوایز و اطلاعات بیشتر:
{{url}}
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'name' => $this->customer->getName(),
            'url'  => $this->url,
        ]);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->customer),
                $content,
                self::getCode()
            )
        );
    }
}
