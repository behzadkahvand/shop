<?php

namespace App\Service\Notification\DTOs\Customer\Product;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Customer;
use App\Entity\Product;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotifyAvailableSmsNotificationDTO extends AbstractNotificationDTO
{
    private Product $product;

    private Customer $customer;

    public function __construct(Product $product, Customer $customer)
    {
        $this->product  = $product;
        $this->customer = $customer;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::CUSTOMER_PRODUCT_AVAILABLE;
    }

    public static function getSection(): string
    {
        return NotificationSectionDictionary::INVENTORY;
    }

    public static function getNotificationType(): string
    {
        return NotificationTypeDictionary::SMS;
    }

    public static function getVariablesDescription(): array
    {
        return [
            'name'         => 'Customer first name',
            'productLink'  => 'Product link',
            'productTitle' => 'Product title',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{ name }} عزیز
{{ productTitle }} که منتظر موجود شدنش بودید، حالا روی تیمچه موجوده. از این لینک می‌تونید بهش سر بزنید
{{ productLink }}
TEMPLATE;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render(
            $templateEngine,
            $key,
            [
                'name'         => $this->customer->getName(),
                'productLink'  => 'https://timcheh.com/product/tpi-' . $this->product->getId(),
                'productTitle' => $this->product->getTitle(),
            ]
        );

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->customer),
                $content,
                self::getCode()
            )
        );
    }
}
