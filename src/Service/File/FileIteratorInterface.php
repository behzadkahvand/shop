<?php

namespace App\Service\File;

use Iterator;

interface FileIteratorInterface extends Iterator
{
    public function current(): RowAbstract;
}
