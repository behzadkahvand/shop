<?php

namespace App\Tests\Unit\Service\Digikala;

use App\Service\Digikala\DigikalaProductLink;
use App\Tests\Unit\BaseUnitTestCase;

class DigikalaProductLinkTest extends BaseUnitTestCase
{
    public function testShouldGenerateLinkCorrectly(): void
    {
        self::assertEquals(
            'https://api.digikala.com/v1/product/123/',
            DigikalaProductLink::generate(123)
        );
    }
}
