<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Dictionary\OrderStatus;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderStatusLog;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\Order\FirstTimeCallFailedSmsNotificationDTO;
use App\Service\Utils\JalaliCalender;
use App\Tests\Unit\BaseUnitTestCase;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FirstTimeCallFailedSmsNotificationDtoTest extends BaseUnitTestCase
{
    public function testItCanGetCodeSuccessfully(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_CALL_FAILED,
            FirstTimeCallFailedSmsNotificationDTO::getCode()
        );
    }

    public function testItCanGetSectionSuccessfully(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::ORDER,
            FirstTimeCallFailedSmsNotificationDTO::getSection()
        );
    }

    public function testItCanGetNotificationTypeSuccessfully(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            FirstTimeCallFailedSmsNotificationDTO::getNotificationType()
        );
    }

    public function testItCanGetVariablesDescriptionSuccessfully(): void
    {
        self::assertEquals(
            [
                'orderId'  => null,
                'name'     => 'Name of the customer',
                'family'   => 'Family of the customer',
                'callDate' => 'Jalali call date in YYYY/mm/dd format',
                'callTime' => 'Time of calling',
            ],
            FirstTimeCallFailedSmsNotificationDTO::getVariablesDescription()
        );
    }

    public function testItCanGetDefaultTemplateSuccessfully(): void
    {
        self::assertEquals(
            <<<TEMPLATE
تیمچه
{{ name }} عزیز
پردازش سفارش {{ orderId }} نیاز به هماهنگی تلفنی داره. در تاریخ {{ callDate }} ساعت {{ callTime }} با شما تماس گرفتیم اما پاسخگو نبودید.
لطفا تا ۲۴ ساعت آینده با پشتیبانی تیمچه تماس بگیرید تا سفارشتون لغو نشه.
پشتیبانی تیمچه: 02191012959
TEMPLATE,
            FirstTimeCallFailedSmsNotificationDTO::getDefaultTemplate()
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testItCanRenderTemplateAndGetTheMessageSuccessfully(): void
    {
        $orderIdentifier  = 1;
        $customerName     = 'name';
        $customerFamily   = 'family';
        $customerMobile   = '09123456789';
        $renderedTemplate = 'Rendered template';
        $customerFullName = 'full name';
        $customerId       = 1;
        $createdAt        = new DateTime();

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive([
            'getName'     => $customerName,
            'getFamily'   => $customerFamily,
            'getMobile'   => $customerMobile,
            'getFullName' => $customerFullName,
            'getId'       => $customerId,
        ])
                 ->once()
                 ->withNoArgs();

        $orderStatusLog1 = Mockery::mock(OrderStatusLog::class);
        $orderStatusLog1->shouldReceive('getStatusTo')->once()->withNoArgs()->andReturn(OrderStatus::CALL_FAILED);
        $orderStatusLog1->shouldReceive('getCreatedAt')->once()->withNoArgs()->andReturn($createdAt);

        $orderStatusLog2 = Mockery::mock(OrderStatusLog::class);
        $orderStatusLog2->shouldReceive('getStatusTo')->once()->withNoArgs()->andReturn(OrderStatus::CALL_FAILED);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive([
            'getCustomer'        => $customer,
            'getIdentifier'      => $orderIdentifier,
            'getOrderStatusLogs' => new ArrayCollection([$orderStatusLog1, $orderStatusLog2]),
        ])
              ->withNoArgs();

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'orderId'  => $orderIdentifier,
                           'name'     => $customerName,
                           'family'   => $customerFamily,
                           'callDate' => JalaliCalender::toJalali(
                               $createdAt->format('Y'),
                               $createdAt->format('m'),
                               $createdAt->format('d')
                           ),
                           'callTime' => $createdAt->format('H:i'),
                       ])
                       ->andReturn($renderedTemplate);

        $message = (new FirstTimeCallFailedSmsNotificationDTO($order))->getMessage($templateEngine, 'ABc123');

        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertEquals($customerFullName, $recipient->getName());
        self::assertEquals($customerId, $recipient->getUserId());
        self::assertEquals($renderedTemplate, $message->getWrappedMessage()->getMessage());
    }
}
