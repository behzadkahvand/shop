<?php

namespace App\Service\Notification\DTOs\Customer\Payment;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Order;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FailedPaymentSmsNotificationDTO extends AbstractNotificationDTO
{
    public function __construct(protected Order $order)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_FAILED_PAYMENT;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::PAYMENT;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'orderId' => null,
            'name'    => 'Name of the customer',
            'family'  => 'Family of the customer',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{ name }} عزیز
متاسفانه پرداخت شما ناموفق بود. ما اقلام سفارش شما به شماره  {{ orderId }} رو تا یک ساعت توی سبد خریدتون رزرو می‌کنیم.
پس لطفا ظرف یک ساعت آینده به سبد خریدتون سر بزنید و پرداخت رو انجام بدید.

https://timcheh.com/dashboard/orders/{{ orderId  }}
TEMPLATE;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getMessage(Environment $templateEngine, string $key): object
    {
        $customer = $this->order->getCustomer();

        $content = $this->render($templateEngine, $key, [
            'orderId' => $this->order->getIdentifier(),
            'name'    => $customer->getName(),
            'family'  => $customer->getFamily(),
        ]);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($customer),
                $content,
                self::getCode()
            )
        );
    }
}
