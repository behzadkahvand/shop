<?php

/**
 * User: amir
 * Date: 1/16/21
 * Time: 2:11 PM
 */

namespace App\Dictionary;

class InventoryUpdateDemandStatus
{
    public const PENDING = 'PENDING';
    public const INITIALIZING = 'INITIALIZING';
    public const INITIALIZED = 'INITIALIZED';

    public const ALL = [
        self::PENDING,
        self::INITIALIZING,
        self::INITIALIZED,
    ];
}
