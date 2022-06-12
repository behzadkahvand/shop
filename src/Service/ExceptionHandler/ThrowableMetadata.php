<?php

namespace App\Service\ExceptionHandler;

final class ThrowableMetadata
{
    private bool $isVisibleForUsers;
    private string $title;
    private int $statusCode;

    public function __construct(bool $isVisibleForUsers, int $statusCode, string $title)
    {
        $this->isVisibleForUsers = $isVisibleForUsers;
        $this->title = $title;
        $this->statusCode = $statusCode;
    }

    public function isVisibleForUsers(): bool
    {
        return $this->isVisibleForUsers;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
