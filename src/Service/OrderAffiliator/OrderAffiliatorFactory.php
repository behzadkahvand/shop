<?php

namespace App\Service\OrderAffiliator;

use App\Service\OrderAffiliator\Exceptions\InvalidUtmSourceException;
use App\Service\OrderAffiliator\PurchaseRequest\AffiliatorPurchaseRequestInterface;
use App\Service\OrderAffiliator\PurchaseRequest\TakhfifanPurchaseRequest;
use Psr\Container\ContainerInterface;

class OrderAffiliatorFactory
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAffiliatorPurchaseRequest(string $utmSource): AffiliatorPurchaseRequestInterface
    {
        switch ($utmSource) {
            case TakhfifanPurchaseRequest::NAME:
                $class = TakhfifanPurchaseRequest::class;
                break;
            default:
                throw new InvalidUtmSourceException();
        }

        return $this->container->get($class);
    }
}
