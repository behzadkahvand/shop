<?php

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class CodeToSlugTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        return $value === null ? null : str_slug($value);
    }
}
