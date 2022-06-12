<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Entity\Product;
use App\Service\Product\Update\PropertyUpdater;

class SpecificationsUpdater implements PropertyUpdater
{
    public function update(Product $product, array $dkp): void
    {
        $specifications = $dkp['product']['specifications'][0]['attributes'];
        if (null === $specifications) {
            return;
        }

        $product
            ->setSpecifications($specifications)
            ->setEAV($this->transformToEav($specifications));
    }

    private function transformToEav(array $specifications): string
    {
        $eav = '';
        foreach ($specifications as $specification) {
            $title = $specification['title'];
            $values = $specification['values'] ?? [];
            $firstValue = $values[0] ?? '';
            $eav .= '<li class="item">
                        <span class="label_feature">' . $title . '</span>
                        <span class="value">' . $firstValue . '</span>
                     </li>';

            unset($values[0]);

            foreach ($values as $value) {
                $eav .= '<li class="item">
                        <span class="label_feature" style="background-color: inherit"></span>
                        <span class="value">' . $value . '</span>
                     </li>';
            }
        }

        return '<div class="product_features_holder">
                    <div class="features_box">
                        <ul class="features_list">
                        ' . $eav . '
                        </ul>
                    </div>
                </div>';
    }
}
