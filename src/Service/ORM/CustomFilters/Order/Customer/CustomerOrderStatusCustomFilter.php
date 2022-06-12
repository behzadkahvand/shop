<?php

namespace App\Service\ORM\CustomFilters\Order\Customer;

use App\Dictionary\CustomerOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\OrderStatusException;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;

final class CustomerOrderStatusCustomFilter implements CustomFilterInterface
{
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();

        if (!isset($queryParams['filter']['status'])) {
            return;
        }

        $orderStatus = $queryParams['filter']['status'];

        if (is_array($orderStatus)) {
            $orderStatus = current($orderStatus);
        }

        if (!CustomerOrderStatus::isValid($orderStatus)) {
            throw new OrderStatusException('Order status is invalid!');
        }

        $customerOrderStatuses = collect(CustomerOrderStatus::toArray())->mapToGroups(
            fn($value, $key) => [$value => $key]
        )->toArray();

        $queryParams['filter']['status'] = ['in' => implode(',', $customerOrderStatuses[$orderStatus])];

        $request->query->replace($queryParams);
    }
}
