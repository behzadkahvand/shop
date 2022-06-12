<?php

namespace App\Service\Notification\DTOs\Customer\ReturnRequest;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

class ReturnRequestApprovedNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(protected Customer $customer)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::RETURN_REQUEST_REGISTERED;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::RETURN_REQUEST;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'name' => 'customer name',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{name}} عزیز
درخواست مرجوعی شما ثبت گردید.
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'name' => $this->customer->getName(),
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
