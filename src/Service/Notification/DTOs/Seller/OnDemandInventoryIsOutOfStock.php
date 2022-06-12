<?php

namespace App\Service\Notification\DTOs\Seller;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Inventory;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;

class OnDemandInventoryIsOutOfStock extends AbstractNotificationDTO
{
    public function __construct(protected Inventory $inventory)
    {
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_ON_DEMAND_INVENTORY_FINISHED;
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
            'inventoryId' => 'inventory id',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
فروشنده عزیز،
موجودی کالای {{ inventoryId }} شما در سایت تیمچه به اتمام رسیده است.
برای سرعت بخشیدن به روند فروش خود ، لطفا هرچه سریعتر اقدام به  افزایش موجودی این کالا نمائید.
ارتباط با تیمچه: 02191012959 
داخلی 432 تا 434 (پشتیبانی فروشندگان)
sellersupport@timcheh.com  
TEMPLATE;
    }

    public function getMessage(Environment $templateEngine, string $key): object
    {
        $content = $this->render(
            $templateEngine,
            $key,
            [
                'inventoryId' => $this->inventory->getId(),
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
