<?php

namespace App\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

/**
 * Class SendOrderSurveySmsNotificationDTO
 */
final class SendOrderSurveySmsNotificationDTO extends AbstractNotificationDTO
{
    private Customer $customer;

    private string $surveyLink;

    public function __construct(Customer $customer, string $surveyLink)
    {
        $this->customer   = $customer;
        $this->surveyLink = $surveyLink;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_SEND_ORDER_SURVEY;
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
            'name'       => 'Customer first name',
            'surveyLink' => 'Survey link',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
{{ name }} عزیز، ممنونیم که تیمچه رو انتخاب کردی.
لطفاً با پاسخ به نظرسنجی زیر، ما رو در بهبود خدمات‌مون همراهی کن.
{{ surveyLink }}

پشتیبانی تیمچه:۰۲۱۹۱۰۱۲۹۵۹
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'name'       => $this->customer->getName(),
            'surveyLink' => $this->surveyLink,
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
