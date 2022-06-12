<?php

namespace App\Logger\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

class GraylogTagProcessor
{
    public function __construct(private RequestStack $requestStack, private string $graylogTag)
    {
    }

    public function __invoke(array $record): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request) {
            $record['extra']['tag'] = $this->graylogTag;
        }

        return $record;
    }
}
