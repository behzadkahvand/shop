<?php

namespace App\Service\Product\Seller;

use App\Entity\Seller;
use App\Service\Product\Seller\Adapters\MetaResolverInterface;

class ProductMetaProvider
{
    private iterable $resolvers;

    public function __construct(iterable $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(Seller $seller)
    {
        $meta = [];
        /** @var MetaResolverInterface $resolver */
        foreach ($this->resolvers as $resolver) {
            $meta = array_merge($meta, $resolver->resolve($seller));
        }

        return $meta;
    }
}
