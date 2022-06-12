<?php

namespace App\Dictionary\Temporary;

use App\Dictionary\Dictionary;

class ShippingMethodPrices extends Dictionary
{
    public const TEHRAN = [
        'NORMAL_EXPRESS'      => 0,
        'HEAVY_EXPRESS'       => 0,
        'SUPER_HEAVY_EXPRESS' => 0,
        'FMCG_EXPRESS'        => 0,
    ];
    public const OTHER_CITIES = [
        'POST'      => 0,
        'PORTERAGE' => 0,
    ];
}
