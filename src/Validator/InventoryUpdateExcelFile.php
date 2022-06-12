<?php

namespace App\Validator;

use App\Entity\Seller;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InventoryUpdateExcelFile extends Constraint
{
    public ?Seller $seller = null;

    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'درخواست مرتبط با فایل "{{ value }}" پیدا نشد.';
}
