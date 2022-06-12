<?php

namespace App\Tests\Unit\Service\Product\Update\PropertyUpdater;

use App\Entity\Product;
use App\Service\Product\Update\PropertyUpdaters\SpecificationsUpdater;
use App\Tests\TestDoubles\Fakes\FakeDigikalaDkp;
use App\Tests\Unit\BaseUnitTestCase;

class SpecificationsUpdaterTest extends BaseUnitTestCase
{
    public function testShouldUpdateEavOnProductCorrectly(): void
    {
        $product = new Product();

        $sut = new SpecificationsUpdater();

        $dkp = FakeDigikalaDkp::build();

        $sut->update($product, $dkp);

        self::assertEquals($dkp['product']['specifications'][0]['attributes'], $product->getSpecifications());
        self::assertEquals(
            $this->removeSpacesAndNewLines($this->getExpectedEav()),
            $this->removeSpacesAndNewLines($product->getEAV())
        );
    }

    public function testShouldSkipIfAttributesIsNull(): void
    {
        $product = new Product();

        $sut = new SpecificationsUpdater();

        $dkp = FakeDigikalaDkp::build();

        $dkp['product']['specifications'][0]['attributes'] = null;

        $sut->update($product, $dkp);

        self::assertEmpty($product->getSpecifications());
        self::assertNull($product->getEAV());
    }

    public function getExpectedEav(): string
    {
        return
            '<div class="product_features_holder">
                <div class="features_box">
                    <ul class="features_list">
                        <li class="item">
                            <span class="label_feature">name1</span>
                            <span class="value">value1</span>
                        </li>
                         <li class="item">
                            <span class="label_feature">name2</span>
                            <span class="value">value2_1</span>
                         </li>
                         <li class="item">
                            <span class="label_feature" style="background-color: inherit"></span>
                            <span class="value">value2_2</span>
                         </li>
                         <li class="item">
                            <span class="label_feature" style="background-color: inherit"></span>
                            <span class="value">value2_3</span>
                         </li>
                         <li class="item">
                            <span class="label_feature">name3</span>
                            <span class="value">value3</span>
                         </li>
                    </ul>
                </div>
            </div>';
    }

    private function removeSpacesAndNewLines(string $string): string
    {
        return trim(preg_replace('/\s\s+/', '', $string));
    }
}
