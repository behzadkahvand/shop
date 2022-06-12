<?php

namespace App\Service\Layout\Block;

abstract class Block implements BlockInterface
{
    protected function get(array $data, string $key): array
    {
        return array_key_exists($key, $data) ? (array) $data[$key] : [];
    }
}
