<?php

namespace App\Service\ORM\CustomFilters\Order\Admin;

use App\Dictionary\OrderBalanceStatus;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderBalanceStatusCustomFilter
 */
final class OrderBalanceStatusCustomFilter implements CustomFilterInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();

        if (!isset($queryParams['filter']['balanceStatus'])) {
            return;
        }

        $balanceStatus = $queryParams['filter']['balanceStatus'];

        unset($queryParams['filter']['balanceStatus']);

        if ($balanceStatus === OrderBalanceStatus::CREDITOR) {
            $queryParams['filter']['balanceAmount']['gt'] = 0;
        } elseif ($balanceStatus === OrderBalanceStatus::DEBTOR) {
            $queryParams['filter']['balanceAmount']['lt'] = 0;
        } else {
            $queryParams['filter']['balanceAmount'] = 0;
        }

        $request->query->replace($queryParams);
    }
}
