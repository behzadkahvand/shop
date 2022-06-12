<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\SellerOrderItem\Seller;

use App\Dictionary\SellerOrderItemStatusMappingDictionary;
use App\Service\ORM\CustomFilters\SellerOrderItem\Seller\SellerOrderItemStatusMappingCustomFilter;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderItemStatusException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SellerOrderItemStatusMappingCustomFilterTest
 */
final class SellerOrderItemStatusMappingCustomFilterTest extends TestCase
{
    public function testItApplyDefaultStatuses()
    {
        $request = new Request();

        $customFilter = new SellerOrderItemStatusMappingCustomFilter();

        $customFilter->apply($request);

        self::assertEquals([
            'filter' => [
                'status' => [
                    'in' => 'SENT_BY_SELLER,DELIVERED,RECEIVED,FULFILLING,SENT_TO_CUSTOMER,STORAGED,CANCELED_BY_USER,CANCELED_BY_SELLER,RETURNED,RETURNING,WAITING',
                ],
            ],
        ], $request->query->all());
    }

    public function testItThrowExceptionIfInvalidStatusIsGiven()
    {
        $request      = new Request(['filter' => ['status' => 'invalid']]);
        $customFilter = new SellerOrderItemStatusMappingCustomFilter();

        $this->expectException(InvalidSellerOrderItemStatusException::class);

        $customFilter->apply($request);
    }

    /**
     * @dataProvider singleStatusProvider
     */
    public function testItApplyFilterForSingleStatus(string $mappedStatus, string $expectedStatuses)
    {
        $request      = new Request(['filter' => ['status' => $mappedStatus]]);
        $customFilter = new SellerOrderItemStatusMappingCustomFilter();

        $customFilter->apply($request);

        $actualFilters = $request->query->all();

        self::assertEquals(['filter' => ['status' => ['in' => $expectedStatuses]]], $actualFilters);
    }

    public function singleStatusProvider()
    {
        $statuses = collect(SellerOrderItemStatusMappingDictionary::toArray())->mapToGroups(fn($v, $k) => [$v => $k])
                                                                              ->toArray();
        foreach ($statuses as $status => $expectedStatuses) {
            yield [$status, implode(',', $expectedStatuses)];
        }
    }

    public function testItApplyFilterForMultipleStatus()
    {
        $request      = new Request(['filter' => ['status' => ['in' => 'CANCELED,WAITING_FOR_SEND']]]);
        $customFilter = new SellerOrderItemStatusMappingCustomFilter();

        $customFilter->apply($request);

        $actualFilters = $request->query->all();

        self::assertEquals([
            'filter' => ['status' => ['in' => 'CANCELED_BY_USER,CANCELED_BY_SELLER,WAITING_FOR_SEND']]
        ], $actualFilters);
    }
}
