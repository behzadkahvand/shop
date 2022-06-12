<?php

namespace App\Service\Utils;

class GenerateSoeMetadata
{
    public const TITLE_PREFIX = "قیمت و خرید انواع";
    public const TITLE_POSTFIX = "| بهترین قیمت | تیمچه";
    public const META_DESCRIPTION_PREFIX = "بررسی مشخصات، قیمت و خرید جدیدترین مدل های";
    public const META_DESCRIPTION_POSTFIX = "| ضمانت اصالت کالا، خرید آنلاین با بهترین قیمت | فروشگاه اینترنتی تیمچه";

    public function title(string $categoryName, ?string $brandName = null): string
    {
        if ($brandName) {
            return sprintf("%s %s %s %s", self::TITLE_PREFIX, $categoryName, $brandName, self::TITLE_POSTFIX);
        }
        return sprintf("%s %s %s", self::TITLE_PREFIX, $categoryName, self::TITLE_POSTFIX);
    }

    public function metaDescription(string $categoryName, ?string $brandName = null): string
    {
        if ($brandName) {
            return sprintf("%s %s %s %s", self::META_DESCRIPTION_PREFIX, $categoryName, $brandName, self::META_DESCRIPTION_POSTFIX);
        }
        return sprintf("%s %s %s", self::META_DESCRIPTION_PREFIX, $categoryName, self::META_DESCRIPTION_POSTFIX);
    }
}
