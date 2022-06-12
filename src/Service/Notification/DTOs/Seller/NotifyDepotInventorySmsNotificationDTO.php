<?php

namespace App\Service\Notification\DTOs\Seller;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Inventory;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotifyDepotInventorySmsNotificationDTO extends AbstractNotificationDTO
{
    private Inventory $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_DEPOT_INVENTORY;
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
            'productCodeAndName' => 'Product code and name',
            'stock'              => 'Product stock',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
فروشنده عزیز،
موجودی دپوی کالای شما {{ productCodeAndName }} در انبار تیمچه به  {{ stock }} عدد رسیده است.
برای سرعت بخشیدن به روند فروش خود ، لطفا هرچه سریعتر اقدام به  افزایش موجودی این کالا نمائید.
ارتباط با تیمچه: 02191012959 
داخلی: 431 – 432 – 433 - 434
ایمیل: sellersupport@timcheh.com  
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
                'stock'              => $this->inventory->getSellerStock(),
                'productCodeAndName' => $this->inventory->getVariant()->getTitle(),
            ]
        );

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($this->inventory->getSeller()),
                $content,
                self::getCode()
            )
        );
    }
}
