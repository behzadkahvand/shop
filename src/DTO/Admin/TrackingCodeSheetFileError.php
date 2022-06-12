<?php

namespace App\DTO\Admin;

use App\DTO\BaseDTO;

class TrackingCodeSheetFileError extends BaseDTO
{
    protected string $errorMessage;

    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
