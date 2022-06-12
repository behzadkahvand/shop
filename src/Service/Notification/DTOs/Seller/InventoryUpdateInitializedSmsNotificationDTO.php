<?php

namespace App\Service\Notification\DTOs\Seller;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\InventoryUpdateDemand;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class InventoryUpdateInitializedSmsNotificationDTO extends AbstractNotificationDTO
{
    protected InventoryUpdateDemand $inventoryUpdateDemand;

    public function __construct(InventoryUpdateDemand $inventoryUpdateDemand)
    {
        $this->inventoryUpdateDemand = $inventoryUpdateDemand;
    }

    public static function getCode(): string
    {
        return NotificationCodeDictionary::SELLER_UPDATE_INVENTORY;
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
            'sellerName' => 'Name of seller',
        ];
    }

    public static function getDefaultTemplate(): string
    {
        return <<<TEMPLATE
تیمچه
{{sellerName}} عزیز،
فایل اکسل درخواستی آماده گردید.برای مشاهده لطفا به منوی گزارشات مراجعه نمایید.

TEMPLATE;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getMessage(Environment $templateEngine, string $key): object
    {
        $seller  = $this->inventoryUpdateDemand->getSeller();
        $content = $this->render($templateEngine, $key, [
            'sellerName' => $seller->getName(),
        ]);

        return async_message(
            new SmsNotification(
                $this->makeRecipientFactory()->make($seller),
                $content,
                self::getCode()
            )
        );
    }
}
