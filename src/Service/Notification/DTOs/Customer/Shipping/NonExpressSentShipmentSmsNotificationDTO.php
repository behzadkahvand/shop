<?php

namespace App\Service\Notification\DTOs\Customer\Shipping;

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

class NonExpressSentShipmentSmsNotificationDTO extends AbstractNotificationDTO
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_POST_SHIPPING;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::ORDER_SHIPMENT;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'orderId' => null,
            'name' => 'Name of the customer',
            'family' => 'Family of the customer',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{ name }} عزیز
سفارش کد {{ orderId }} به پست تحویل شد.
می‌تونید وضعیت سفارشتون رو از این لینک پیگیری کنید

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
            'name' => $customer->getName(),
            'family' => $customer->getFamily(),
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
