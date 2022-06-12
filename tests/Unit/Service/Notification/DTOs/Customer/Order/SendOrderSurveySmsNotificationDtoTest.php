<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\Order;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\Order\SendOrderSurveySmsNotificationDTO;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SendOrderSurveySmsNotificationDtoTest extends BaseUnitTestCase
{
    public function testItCanGetCodeSuccessfully(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_SEND_ORDER_SURVEY,
            SendOrderSurveySmsNotificationDTO::getCode()
        );
    }

    public function testItCanGetSectionSuccessfully(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::ORDER,
            SendOrderSurveySmsNotificationDTO::getSection()
        );
    }

    public function testItCanGetNotificationTypeSuccessfully(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            SendOrderSurveySmsNotificationDTO::getNotificationType()
        );
    }

    public function testItCanGetVariablesDescriptionSuccessfully(): void
    {
        self::assertEquals([
            'name'       => 'Customer first name',
            'surveyLink' => 'Survey link',
        ], SendOrderSurveySmsNotificationDTO::getVariablesDescription());
    }

    public function testItCanGetDefaultTemplateSuccessfully(): void
    {
        self::assertEquals(
            <<<TEMPLATE
{{ name }} عزیز، ممنونیم که تیمچه رو انتخاب کردی.
لطفاً با پاسخ به نظرسنجی زیر، ما رو در بهبود خدمات‌مون همراهی کن.
{{ surveyLink }}

پشتیبانی تیمچه:۰۲۱۹۱۰۱۲۹۵۹
TEMPLATE,
            SendOrderSurveySmsNotificationDTO::getDefaultTemplate()
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testItCanRenderTemplateAndGetTheMessageSuccessfully(): void
    {
        $customerName     = 'name';
        $surveyLink       = 'Rendered template';
        $customerMobile   = '09123456789';
        $customerFullName = 'full name';
        $customerId       = 1;

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'name'       => $customerName,
                           'surveyLink' => 'Rendered template',
                       ])
                       ->andReturn($surveyLink);

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive([
            'getName'     => $customerName,
            'getMobile'   => $customerMobile,
            'getFullName' => $customerFullName,
            'getId'       => $customerId,
        ])
                 ->once()
                 ->withNoArgs();

        $DTO     = new SendOrderSurveySmsNotificationDTO($customer, $surveyLink);
        $message = ($DTO)->getMessage($templateEngine, 'ABc123');

        self::assertInstanceOf(AsyncMessage::class, $message);
        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertEquals($customerFullName, $recipient->getName());
        self::assertEquals($customerId, $recipient->getUserId());
        self::assertEquals(get_class($customer), $recipient->getUserType());
        self::assertEquals($surveyLink, $message->getWrappedMessage()->getMessage());
    }
}
