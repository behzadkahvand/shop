<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class PersianEnglishChars extends Constraint
{
    public string $messages = 'must be a persian or english chars.';
}
