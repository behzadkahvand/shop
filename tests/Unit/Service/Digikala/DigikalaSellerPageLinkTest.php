<?php

namespace App\Tests\Unit\Service\Digikala;

use App\Service\Digikala\DigikalaSellerPageLink;
use App\Tests\Unit\BaseUnitTestCase;

class DigikalaSellerPageLinkTest extends BaseUnitTestCase
{
    public function testShouldGenerateLinkCorrectly(): void
    {
        self::assertEquals(
            'https://api.digikala.com/v1/sellers/fake-seller/?page=2',
            DigikalaSellerPageLink::generate('fake-seller', 2)
        );
    }
}
