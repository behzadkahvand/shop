<?php

namespace App\Tests\Unit\Service\Notification\DTOs\Customer\RateAndReview;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Customer\RateAndReview\RateAndReviewSmsNotificationDTO;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Twig\Environment;

class RateAndReviewSmsNotificationDTOTest extends BaseUnitTestCase
{
    public function testItCanGetCode(): void
    {
        self::assertEquals(
            NotificationCodeDictionary::CUSTOMER_RATE_AND_REVIEW,
            RateAndReviewSmsNotificationDTO::getCode()
        );
    }

    public function testItCanGetSection(): void
    {
        self::assertEquals(
            NotificationSectionDictionary::PRODUCT_RATE_AND_REVIEW,
            RateAndReviewSmsNotificationDTO::getSection()
        );
    }

    public function testItCanGetNotificationType(): void
    {
        self::assertEquals(
            NotificationTypeDictionary::SMS,
            RateAndReviewSmsNotificationDTO::getNotificationType()
        );
    }

    public function testItCanGetVariablesDescription(): void
    {
        self::assertEquals(
            [
                'name' => 'Customer first name',
                'url'  => 'Link to submit a product rate and review',
            ],
            RateAndReviewSmsNotificationDTO::getVariablesDescription()
        );
    }

    public function testItCanGetDefaultTemplate(): void
    {
        self::assertEquals(
            <<<TEMPLATE
تیمچه
{{name}} عزیز
از شما دعوت می‌کنیم نظرتون رو در مورد کالاهایی که از تیمچه خریدید، ثبت کنید.
پیشاپیش از اینکه با ثبت نظر و انتقال تجریه خودتون به انتخاب بقیه کمک می‌کنید ممنونیم.
جوایز و اطلاعات بیشتر:
{{url}}
TEMPLATE,
            RateAndReviewSmsNotificationDTO::getDefaultTemplate()
        );
    }

    public function testItCanRenderTemplateAndGetTheMessage(): void
    {
        $customerMobile   = '09121111111';
        $customerFullName = 'getFullName';
        $customerId       = 1;
        $renderedTemplate = 'Rendered template';

        $customerMock = Mockery::mock(Customer::class);
        $customerMock->expects('getName')->andReturn('test');
        $customerMock->expects('getMobile')->andReturn($customerMobile);
        $customerMock->expects('getFullName')->andReturn($customerFullName);
        $customerMock->expects('getId')->andReturn($customerId);

        $templateEngine = Mockery::mock(Environment::class);
        $templateEngine->shouldReceive('render')
                       ->once()
                       ->with(Mockery::type('string'), [
                           'name' => 'test',
                           'url'  => 'url',
                       ])
                       ->andReturn($renderedTemplate);

        $message = (new RateAndReviewSmsNotificationDTO($customerMock, 'url'))->getMessage($templateEngine, '123456');

        self::assertInstanceOf(AsyncMessage::class, $message);
        $recipient = $message->getWrappedMessage()->getRecipient();
        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertEquals($customerMobile, $recipient->getMobile());
        self::assertEquals($customerFullName, $recipient->getName());
        self::assertEquals($customerId, $recipient->getUserId());
        self::assertEquals($renderedTemplate, $message->getWrappedMessage()->getMessage());
    }
}
