<?php

namespace App\Service\ORM\CustomFilters\SellerPackage\Seller;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class SellerPackagesCustomFilter
 */
final class SellerPackagesCustomFilter implements CustomFilterInterface
{
    private Security $security;

    /**
     * SellerPackagesCustomFilter constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $request->query->replace(array_replace_recursive($request->query->all(), [
            'filter' => [
                'seller.id' => $this->security->getUser()->getId(),
            ],
        ]));
    }
}
