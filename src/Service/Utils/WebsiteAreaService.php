<?php

namespace App\Service\Utils;

use App\Dictionary\WebsiteAreaDictionary;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class WebsiteAreaService
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function getArea(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return null;
        }

        return $request->attributes->get('website_area');
    }

    public function isArea(string $area): bool
    {
        return in_array($area, WebsiteAreaDictionary::toArray()) && $area === $this->getArea();
    }

    public function isAdminArea(): bool
    {
        return $this->isArea(WebsiteAreaDictionary::AREA_ADMIN);
    }

    public function isCustomerArea(): bool
    {
        return $this->isArea(WebsiteAreaDictionary::AREA_CUSTOMER);
    }

    public function isSellerArea(): bool
    {
        return $this->isArea(WebsiteAreaDictionary::AREA_SELLER);
    }
}
