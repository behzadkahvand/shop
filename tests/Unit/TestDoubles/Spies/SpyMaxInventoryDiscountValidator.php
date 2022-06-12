<?php

namespace App\Tests\Unit\TestDoubles\Spies;

use App\Service\Discount\MaxInventoryDiscountValidator;

class SpyMaxInventoryDiscountValidator extends MaxInventoryDiscountValidator
{
    public int $limit;
}
