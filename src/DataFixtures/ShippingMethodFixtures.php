<?php

namespace App\DataFixtures;

use App\Entity\ShippingMethod;

class ShippingMethodFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'shipping_method_1',
            $this->createShippingMethod(
                'پست',
                'POST'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_2',
            $this->createShippingMethod(
                'باربری',
                'PORTERAGE'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_3',
            $this->createShippingMethod(
                'اکسپرس تیمچه - عادی',
                'NORMAL_EXPRESS'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_4',
            $this->createShippingMethod(
                'اکسپرس تیمچه - سنگین',
                'HEAVY_EXPRESS',
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_5',
            $this->createShippingMethod(
                'اکسپرس تیمچه - فوق سنگین',
                'SUPER_HEAVY_EXPRESS',
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_6',
            $this->createShippingMethod(
                'اکسپرس تیمچه - سوپر مارکتی',
                'FMCG_EXPRESS',
            )
        );

        $this->manager->flush();
    }

    private function createShippingMethod(string $name, string $code): ShippingMethod
    {
        return (new ShippingMethod())->setName($name)->setCode($code);
    }
}
