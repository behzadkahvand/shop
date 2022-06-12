<?php

namespace App\Service\ORM\CustomFilters\ProductVariant\Seller;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class SellerProductVariantsCustomFilter
 */
final class SellerProductVariantsCustomFilter implements CustomFilterInterface
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * SellerProductVariantsCustomFilter constructor.
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
        $queryParams = array_replace_recursive($request->query->all(), [
            'filter' => [
                'inventories.seller.id' => $this->security->getUser()->getId(),
            ],
        ]);

        $request->query->replace($queryParams);
    }
}
