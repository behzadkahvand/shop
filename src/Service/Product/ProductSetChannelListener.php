<?php

namespace App\Service\Product;

use App\Dictionary\ProductChannelDictionary;
use App\Entity\Admin;
use App\Entity\Product;
use App\Entity\Seller;
use Symfony\Component\Security\Core\Security;

class ProductSetChannelListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onProductPrePersist(Product $product)
    {
        if (!$user = $this->security->getUser()) {
            return;
        }

        if ($user instanceof Admin) {
            $product->setChannel(ProductChannelDictionary::ADMIN);

            return;
        }

        if ($user instanceof Seller) {
            $product->setChannel(ProductChannelDictionary::SELLER);

            return;
        }

        throw new \Exception('The channel is not supported.');
    }
}
