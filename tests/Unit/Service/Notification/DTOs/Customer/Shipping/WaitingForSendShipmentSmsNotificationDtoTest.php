<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\Shipping;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Entity\Order;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSendShipmentSmsNotificationDTO;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class WaitingForSendShipmentSmsNotificationDtoTest extends BaseUnitTestCase
{
    public function testItCanGetCodeSuccessfully(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_WAITING_FOR_SHIPMENT,
            WaitingForSendShipmentSmsNotificationDTO::getCode()
        );
    }

    public function testItCanGetSectionSuccessfully(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::ORDER_SHIPMENT,
            WaitingForSendShipmentSmsNotificationDTO::getSection()
        );
    }

    public function testItCanGetNotificationTypeSuccessfully(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            WaitingForSendShipmentSmsNotificationDTO::getNotificationType()
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
            WaitingForSendShipmentSmsNotificationDTO::getVariablesDescription()
        );
    }

    public function testItCanGetDefaultTemplateSuccessfully(): void
    {
        self::assertEquals(
            <<<TEMPLATE
تیمچه
{{ name }} عزیز
سفارش کد {{ orderId }} پردازش و آماده ارسال شد.
می‌تونید وضعیت سفارشتون رو از این لینک پیگیری کنید.

https://timcheh.com/dashboard/orders/{{ orderId  }}
TEMPLATE,
            WaitingForSendShipmentSmsNotificationDTO::getDefaultTemplate()
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

        $message = (new WaitingForSendShipmentSmsNotificationDTO($order))->getMessage($templateEngine, 'ABc123');

        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertEquals($customerFullName, $recipient->getName());
        self::assertEquals($customerId, $recipient->getUserId());
        self::assertEquals($renderedTemplate, $message->getWrappedMessage()->getMessage());
    }
}
