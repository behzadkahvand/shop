<?php

namespace App\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderStatusLog;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Utils\JalaliCalender;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FirstTimeCallFailedSmsNotificationDTO extends AbstractNotificationDTO
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_CALL_FAILED;
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
            'orderId'  => null,
            'name'     => 'Name of the customer',
            'family'   => 'Family of the customer',
            'callDate' => 'Jalali call date in YYYY/mm/dd format',
            'callTime' => 'Time of calling',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{ name }} عزیز
پردازش سفارش {{ orderId }} نیاز به هماهنگی تلفنی داره. در تاریخ {{ callDate }} ساعت {{ callTime }} با شما تماس گرفتیم اما پاسخگو نبودید.
لطفا تا ۲۴ ساعت آینده با پشتیبانی تیمچه تماس بگیرید تا سفارشتون لغو نشه.
پشتیبانی تیمچه: 02191012959
TEMPLATE;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getMessage(Environment $templateEngine, string $key): object
    {
        $closure = fn(OrderStatusLog $log) => $log->getStatusTo() === OrderStatus::CALL_FAILED;

        $customer = $this->order->getCustomer();

        $logs = $this->order->getOrderStatusLogs()->filter($closure);
        $log  = $logs->current();

        $createdAt = $log->getCreatedAt();
        $year      = $createdAt->format('Y');
        $month     = $createdAt->format('m');
        $day       = $createdAt->format('d');

        $content = $this->render($templateEngine, $key, [
            'orderId'  => $this->order->getIdentifier(),
            'name'     => $customer->getName(),
            'family'   => $customer->getFamily(),
            'callDate' => JalaliCalender::toJalali($year, $month, $day),
            'callTime' => $createdAt->format('H:i'),
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
