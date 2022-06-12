<?php

namespace App\Service\File\Text;

use App\Service\File\RowAbstract;

final class Row extends RowAbstract
{
    public string $row;

    private function __construct(string $row)
    {
        $this->row = $row;
    }

    public static function fromString(string $row): self
    {
        return new self($row);
    }
}
