<?php

namespace App\Service\Notification\DTOs\Customer\Wallet;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

class WalletWithdrawNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(protected Customer $customer, protected int $amount)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_WALLET_WITHDRAW;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::WALLET;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'amount' => 'withdraw amount',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
مبلغ {{amount}} .تومان از کیف پول شما برداشت شد
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render($templateEngine, $key, [
            'amount' => number_format($this->amount),
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
