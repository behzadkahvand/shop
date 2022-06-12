<?php

namespace App\Dictionary;

class InventoryUpdateSheetStatus
{
    public const PENDING = 'PENDING';
    public const PROCESSING = 'PROCESSING';
    public const PROCESSED = 'PROCESSED';
    public const FAILED = 'FAILED';

    public const ALL = [
        self::PENDING,
        self::PROCESSING,
        self::PROCESSED,
        self::FAILED,
    ];
}
