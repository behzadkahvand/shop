<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\SellerOrderItem\Seller;

use App\Entity\Seller;
use App\Service\ORM\CustomFilters\SellerOrderItem\Seller\SellerOrderItemsCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class SellerOrderItemsCustomFilterTest
 */
final class SellerOrderItemsCustomFilterTest extends MockeryTestCase
{
    public function testItApplySellerIdOnFilters()
    {
        $request = new Request();
        self::assertEmpty($request->query->all());

        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')->once()->withNoArgs()->andReturnUsing(function () {
            $seller = \Mockery::mock(Seller::class);
            $seller->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

            return $seller;
        });

        $customFilter = new SellerOrderItemsCustomFilter($security);

        $customFilter->apply($request);

        self::assertNotEmpty($request->query->all());
        self::assertEquals([
            'filter' => [
                'seller.id' => 1,
            ],
        ], $request->query->all());
    }
}
