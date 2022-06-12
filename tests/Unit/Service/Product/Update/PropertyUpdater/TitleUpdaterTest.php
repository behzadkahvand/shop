<?php

namespace App\Tests\Unit\Service\Product\Update\PropertyUpdater;

use App\Entity\Product;
use App\Service\Product\Update\PropertyUpdaters\TitleUpdater;
use App\Tests\TestDoubles\Fakes\FakeDigikalaDkp;
use App\Tests\Unit\BaseUnitTestCase;

class TitleUpdaterTest extends BaseUnitTestCase
{
    public function testShouldUpdateProductTitleCorrectly(): void
    {
        $product = new Product();

        $sut = new TitleUpdater();

        $sut->update($product, FakeDigikalaDkp::build());

        self::assertEquals('dummy product', $product->getTitle());
    }
}
