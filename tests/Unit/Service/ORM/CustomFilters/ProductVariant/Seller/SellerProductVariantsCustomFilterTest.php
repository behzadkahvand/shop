<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\ProductVariant\Seller;

use App\Service\ORM\CustomFilters\ProductVariant\Seller\SellerProductVariantsCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SellerProductVariantsCustomFilterTest
 */
final class SellerProductVariantsCustomFilterTest extends MockeryTestCase
{
    public function testItApplySellerProductVariantsCustomFilter()
    {
        $seller = \Mockery::mock(UserInterface::class);
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')->once()->withNoArgs()->andReturn($seller);
        $seller->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $request = new Request();

        $customFilter = new SellerProductVariantsCustomFilter($security);

        $customFilter->apply($request);

        self::assertSame(['filter' => ['inventories.seller.id' => 1]], $request->query->all());
    }
}
