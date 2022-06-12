<?php

namespace App\Service\ORM\CustomFilters\Order\Seller;

use App\Dictionary\SellerOrderStatus;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use Symfony\Component\HttpFoundation\Request;

final class SellerOrderStatusCustomFilter implements CustomFilterInterface
{
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();

        if (!isset($queryParams['filter']['orderStatus'])) {
            return;
        }

        $orderStatus = $queryParams['filter']['orderStatus'];

        if (is_array($orderStatus)) {
            if (!array_key_exists('in', $orderStatus)) {
                throw new InvalidSellerOrderStatusException();
            }

            $orderStatuses = explode(',', $orderStatus['in']);
        } else {
            $orderStatuses = (array) $orderStatus;
        }

        $sellerOrderStatuses = collect(SellerOrderStatus::toArray())->mapToGroups(fn($value, $key) => [$value => $key])
                                                                    ->toArray();
        $statues = collect([]);

        foreach ($orderStatuses as $status) {
            if (!SellerOrderStatus::isValid($status)) {
                throw new InvalidSellerOrderStatusException();
            }

            $statues = $statues->merge($sellerOrderStatuses[$status]);
        }

        $queryParams['filter']['orderItem.order.status']['in'] = $statues->unique()->values()->implode(',');

        unset($queryParams['filter']['orderStatus']);

        $request->query->replace($queryParams);
    }
}
