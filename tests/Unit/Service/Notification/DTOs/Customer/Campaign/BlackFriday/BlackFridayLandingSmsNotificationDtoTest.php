<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\Campaign\BlackFriday;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\Campaign\BlackFriday\BlackFridayLandingSmsNotificationDto;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Twig\Environment;

class BlackFridayLandingSmsNotificationDtoTest extends BaseUnitTestCase
{
    public function testItCanGetCode(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_BLACK_FRIDAY_CAMPAIGN,
            BlackFridayLandingSmsNotificationDto::getCode()
        );
    }

    public function testItCanGetSection(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::CAMPAIGN,
            BlackFridayLandingSmsNotificationDto::getSection()
        );
    }

    public function testItCanGetNotificationType(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            BlackFridayLandingSmsNotificationDto::getNotificationType()
        );
    }

    public function testItCanGetVariablesDescription(): void
    {
        self::assertEmpty(
            BlackFridayLandingSmsNotificationDto::getVariablesDescription()
        );
    }

    public function testItCanGetDefaultTemplate(): void
    {
        self::assertEquals(
            <<<TEMPLATE
دوست خوب تیمچه شماره‌ت با موفقیت ثبت شد.
ما از شروع بلک فرایدی تیمچه با خبرت می‌کنیم.
تا ۹۹ درصد تخفیف در «این جمعه وقتشه»
راستی به اینستاگرام تیمچه هم سر بزن، کلی تخفیف جذاب داریم:
https://instagram.com/timchehcom?utm_medium=copy_link
TEMPLATE,
            BlackFridayLandingSmsNotificationDto::getDefaultTemplate()
        );
    }

    public function testItCanRenderTemplateAndGetTheMessage(): void
    {
        $customerMobile   = '09121111111';
        $renderedTemplate = 'Rendered template';
        $templateEngine   = Mockery::mock(Environment::class);
        $templateEngine->expects('render')
                       ->andReturn($renderedTemplate);

        $DTO     = new BlackFridayLandingSmsNotificationDto($customerMobile);
        $message = ($DTO)->getMessage($templateEngine, '123');

        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertNull($recipient->getName());
        self::assertNull($recipient->getUserId());
        self::assertNull($recipient->getUserType());
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_BLACK_FRIDAY_CAMPAIGN,
            $message->getWrappedMessage()->getCode()
        );
    }
}
