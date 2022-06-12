<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Entity\Order;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\Order\OrderCanceledSmsNotificationDTO;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class OrderCanceledSmsNotificationDtoTest extends BaseUnitTestCase
{
    public function testItCanGetCodeSuccessfully(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_ORDER_CANCELED,
            OrderCanceledSmsNotificationDTO::getCode()
        );
    }

    public function testItCanGetSectionSuccessfully(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::ORDER,
            OrderCanceledSmsNotificationDTO::getSection()
        );
    }

    public function testItCanGetNotificationTypeSuccessfully(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            OrderCanceledSmsNotificationDTO::getNotificationType()
        );
    }

    public function testItCanGetVariablesDescriptionSuccessfully(): void
    {
        self::assertEquals(
            [
                'orderId' => null,
                'name'    => 'Name of the customer',
                'family'  => 'Family of the customer',
            ],
            OrderCanceledSmsNotificationDTO::getVariablesDescription()
        );
    }

    public function testItCanGetDefaultTemplateSuccessfully(): void
    {
        self::assertEquals(
            <<<TEMPLATE
تیمچه
{{ name }} عزیز
سفارش  {{ orderId }} لغو شد.
اگه مبلغ سفارش رو پرداخت کردید، ظرف ۲۴ تا ۷۲ ساعت کاری به حسابتون برمی‌گرده.
لطفا اطلاعات حسابتون رو از طریق پروفایل سایت تیمچه تکمیل کنید ( شماره حسابی که وارد می‌کنید، باید همونی باشه که باهاش خرید کرده بودید).
با تشکر از صبوری شما
TEMPLATE,
            OrderCanceledSmsNotificationDTO::getDefaultTemplate()
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
        $customerFullName = 'full name';
        $customerId       = 1;
        $renderedTemplate = 'Rendered template';

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'orderId' => $orderIdentifier,
                           'name'    => $customerName,
                           'family'  => $customerFamily,
                       ])
                       ->andReturn($renderedTemplate);

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

        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getCustomer' => $customer, 'getIdentifier' => $orderIdentifier])
              ->once()
              ->withNoArgs();

        $message = (new OrderCanceledSmsNotificationDTO($order))->getMessage($templateEngine, 'ABc123');

        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertEquals($customerFullName, $recipient->getName());
        self::assertEquals($customerId, $recipient->getUserId());
        self::assertEquals(get_class($customer), $recipient->getUserType());
        self::assertEquals($renderedTemplate, $message->getWrappedMessage()->getMessage());
    }
}
