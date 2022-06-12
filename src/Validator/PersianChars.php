<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class PersianChars extends Constraint
{
    public array $messages = [
        'alpha'     => 'must be a persian alpahbet.',
        'num'       => 'must be a persian number.',
        'alpha_num' => 'must be a persian alpahbet or number.',
    ];

    public bool $alphaNum = true;
    public bool $alpha = false;
    public bool $num = false;
}
