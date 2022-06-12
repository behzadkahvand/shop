<?php

namespace App\Service\Product\Seller;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class InventoryValidationConstraintsFactory
{
    /**
     * @param bool $overrideMessages
     *
     * @return array<Constraint>
     */
    public function getSellerStockConstraints(bool $checkForCampaign, $overrideMessages = false): array
    {
        if ($checkForCampaign) {
            return [
                new Blank(null, 'موجودی کالاهای کمپین قابل ویرایش نیست'),
                new NotNull(null, 'موجودی کالاهای کمپین قابل ویرایش نیست')
            ];
        }

        return [
            new NotBlank(null, $overrideMessages ? 'فیلد موجودی نباید خالی باشد.' : null),
            new NotNull(null, $overrideMessages ? 'فیلد موجودی نباید خالی باشد.' : null),
            new PositiveOrZero(null, $overrideMessages ? 'فیلد موجودی باید مقدار صفر یا بزرگتر از صفر داشته باشد.' : null),
        ];
    }

    /**
     * @param bool $overrideMessages
     *
     * @return array<Constraint>
     */
    public function getPriceConstraints(bool $checkForCampaign, $overrideMessages = false): array
    {
        if ($checkForCampaign) {
            return [
                new Blank(null, 'قیمت کالاهای کمپین قابل ویرایش نیست'),
                new NotNull(null, 'قیمت کالاهای کمپین قابل ویرایش نیست')
            ];
        }

        return [
            new NotBlank(null, $overrideMessages ? 'فیلد قیمت عرف بازار نباید خالی باشد.' : null),
            new NotNull(null, $overrideMessages ? 'فیلد قیمت عرف بازار نباید خالی باشد.' : null),
            new Positive(null, $overrideMessages ? 'فیلد قیمت عرف بازار باید مقدار صفر یا بزرگتر از صفر داشته باشد.' : null),
            new GreaterThanOrEqual([
                'propertyPath' => 'parent.all[finalPrice].data',
            ], null, $overrideMessages ?  ' قیمت فروش نباید بیشتر از قیمت عرف بازار باشد.' : null),
        ];
    }

    /**
     * @param bool $overrideMessages
     * @param null $updatedBy
     *
     * @return array<Constraint>
     */
    public function getFinalPriceConstraints(bool $checkForCampaign, $overrideMessages = false): array
    {
        if ($checkForCampaign) {
            return [
                new Blank(null, 'قیمت کالاهای کمپین قابل ویرایش نیست'),
                new NotNull(null, 'قیمت کالاهای کمپین قابل ویرایش نیست')
            ];
        }

        return [
            new NotBlank(null, $overrideMessages ? 'فیلد قیمت فروش نباید خالی باشد.' : null),
            new NotNull(null, $overrideMessages ? 'فیلد قیمت فروش نباید خالی باشد.' : null),
            new Positive(null, $overrideMessages ? 'فیلد قیمت فروش باید مقدار صفر یا بزرگتر از صفر داشته باشد.' : null),
            new LessThanOrEqual([
                'propertyPath' => 'parent.all[price].data',
            ], null, $overrideMessages ?  ' قیمت فروش نباید بیشتر از قیمت عرف بازار باشد.' : null),
        ];
    }

    /**
     * @param bool $overrideMessages
     *
     * @return array<Constraint>
     */
    public function getMaxPurchasePerOrderConstraints($overrideMessages = false): array
    {
        return [
            new NotBlank(null, $overrideMessages ? 'فیلد حداکثر سفارش در سبد نباید خالی باشد.' : null),
            new NotNull(null, $overrideMessages ? 'فیلد حداکثر سفارش در سبد نباید خالی باشد.' : null),
            new PositiveOrZero(null, $overrideMessages ? 'فیلد حداکثر سفارش در سبد باید مقدار صفر یا بزرگتر از صفر داشته باشد.' : null),
        ];
    }

    public function getLeadTimeConstraints(int $categoryMaxLead, bool $overrideMessages = false): array
    {
        return [
            new NotBlank(null, $overrideMessages ? 'فیلد زمان ارسال نباید خالی باشد.' : null),
            new NotNull(null, $overrideMessages ? 'فیلد زمان ارسال نباید خالی باشد.' : null),
            new Range([
                'min'            => 0,
                'max'            => $categoryMaxLead,
                'invalidMessage' => "It's not a valid leadTime.",
            ], $overrideMessages ? 'مقدار فیلد زمان ارسال باید بزرگتر یا مساوی {{ min }} و کوچکتر یا مساوی {{ max }} باشد.' : null),
        ];
    }

    /**
     * @param bool $overrideMessages
     *
     * @return array<Constraint>
     */
    public function getStatusConstraints($overrideMessages = false): array
    {
        return [new NotBlank(null, $overrideMessages ? 'فیلد وضعیت نباید خالی باشد.' : null)];
    }

    /**
     * @param bool $overrideMessages
     *
     * @return array<Constraint>
     */
    public function getIsActiveConstraints($overrideMessages = false): array
    {
        return [new NotBlank(null, $overrideMessages ? 'فیلد فعال نباید خالی باشد.' : null)];
    }

    public function getSellerCodeConstraints(bool $overrideMessages = false): array
    {
        return [
            new Regex(
                '/^[a-zA-Z0-9_-]+$/',
                $overrideMessages ? 'فرمت کد کالا اشتباه است، کد کالا مینواند شامل اعداد انگلیسی، حروف انگلیسی، - و ـ باشد' : null
            )
        ];
    }
}
