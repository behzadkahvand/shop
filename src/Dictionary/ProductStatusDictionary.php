<?php

namespace App\Dictionary;

class ProductStatusDictionary extends Dictionary
{
    public const DRAFT = 'DRAFT';

    public const EDITING = 'EDITING';

    public const WAITING_FOR_ACCEPT = 'WAITING_FOR_ACCEPT';

    public const CONFIRMED = 'CONFIRMED';

    public const REJECTED = 'REJECTED';

    public const SOON = 'SOON';

    public const UNAVAILABLE = 'UNAVAILABLE';

    public const SHUTDOWN = 'SHUTDOWN';

    public const TRASHED = 'TRASHED';
}
