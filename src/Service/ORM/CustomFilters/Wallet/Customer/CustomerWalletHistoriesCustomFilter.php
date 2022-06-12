<?php

namespace App\Service\ORM\CustomFilters\Wallet\Customer;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CustomerWalletHistoriesCustomFilter implements CustomFilterInterface
{
    public function __construct(private Security $security)
    {
    }

    public function apply(Request $request): void
    {
        $queryParams = array_replace_recursive($request->query->all(), [
            'filter' => [
                'wallet.id' => $this->security->getUser()->getWallet()->getId(),
            ],
            'sort' => ['-createdAt']
        ]);

        $request->query->replace($queryParams);
    }
}
