<?php

namespace App\Tests\Integration\TestDoubles\Fakes;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class FakeGoutteClient extends Client
{
    protected array $responses = [];

    public function __construct()
    {
    }

    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): Crawler {
        return $this->responses[$uri];
    }

    public function addFakeResponse(string $uri, Crawler $response): void
    {
        $this->responses[$uri] = $response;
    }
}
