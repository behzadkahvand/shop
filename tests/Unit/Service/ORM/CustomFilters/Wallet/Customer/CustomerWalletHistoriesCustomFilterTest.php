<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Wallet\Customer;

use App\Entity\Customer;
use App\Entity\Wallet;
use App\Service\ORM\CustomFilters\Wallet\Customer\CustomerWalletHistoriesCustomFilter;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CustomerWalletHistoriesCustomFilterTest extends BaseUnitTestCase
{
    public function testItCanApplyDefaultSortAndFilterToRequest(): void
    {
        $securityMock = Mockery::mock(Security::class);
        $customerMock = Mockery::mock(Customer::class);
        $walletMock   = Mockery::mock(Wallet::class);

        $securityMock->expects('getUser')->withNoArgs()->andReturns($customerMock);
        $customerMock->expects('getWallet')->withNoArgs()->andReturns($walletMock);
        $walletMock->expects('getId')->withNoArgs()->andReturns(12);

        $sut = new CustomerWalletHistoriesCustomFilter($securityMock);

        $request = new Request(['limit' => 100]);

        $sut->apply($request);

        self::assertSame([
            'limit'  => 100,
            'filter' => [
                'wallet.id' => 12,
            ],
            'sort'   => ['-createdAt']
        ], $request->query->all());
    }
}
