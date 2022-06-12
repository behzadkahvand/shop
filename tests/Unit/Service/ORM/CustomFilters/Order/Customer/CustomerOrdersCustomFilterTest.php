<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Order\Customer;

use App\Service\ORM\CustomFilters\Order\Customer\CustomerOrdersCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CustomerOrdersCustomFilterTest
 */
final class CustomerOrdersCustomFilterTest extends MockeryTestCase
{
    public function testItApplyCustomerOrders()
    {
        $customer = \Mockery::mock(UserInterface::class);
        $customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')->once()->withNoArgs()->andReturn($customer);

        $request      = new Request();
        $customFilter = new CustomerOrdersCustomFilter($security);

        $customFilter->apply($request);

        self::assertSame(['filter' => ['customer.id' => 1]], $request->query->all());
    }
}
