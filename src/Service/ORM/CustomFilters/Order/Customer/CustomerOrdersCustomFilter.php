<?php

namespace App\Service\ORM\CustomFilters\Order\Customer;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class CustomerOrdersCustomFilter
 */
final class CustomerOrdersCustomFilter implements CustomFilterInterface
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * CustomerOrdersCustomFilter constructor.
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
                'customer.id' => $this->security->getUser()->getId(),
            ],
        ]);

        $request->query->replace($queryParams);
    }
}
