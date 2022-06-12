<?php

namespace App\Tests\Unit\Service\Order\Survey\Link;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\Order\Survey\Link\PorslineOrderSurveyLinkGenerator;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class PorslineOrderSurveyLinkGeneratorTest
 */
final class PorslineOrderSurveyLinkGeneratorTest extends MockeryTestCase
{
    public function testItThrowExceptionIfPorslineResponseIsUnexpected(): void
    {
        $orderIdentifier = 123456;

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')->once()->with(false)->andReturn('invalid json');

        $client = \Mockery::mock(HttpClientInterface::class);
        $client->shouldReceive('request')
               ->once()
               ->with('POST', PorslineOrderSurveyLinkGenerator::PORSLINE_ORDER_SURVEY_API, [
                   'json'    => [
                       'values' => [
                           ['identifier' => $orderIdentifier],
                       ],
                   ],
                   'headers' => [
                       'Content-Type'  => 'application/json',
                       'Authorization' => 'API-Key 123456',
                       'Accept'        => 'application/json',
                   ],
               ])
               ->andReturn($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to generate survey link. porsline api returned an unexpected response.');

        $linkGenerator = new PorslineOrderSurveyLinkGenerator($client, '123456');

        $linkGenerator->generateLink($orderIdentifier);
    }

    public function testItThrowExceptionIfPorslineResponseDoesNotHasUrlsKey(): void
    {
        $orderIdentifier = 123456;

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')->once()->with(false)->andReturn('{}');

        $client = \Mockery::mock(HttpClientInterface::class);
        $client->shouldReceive('request')
               ->once()
               ->with('POST', PorslineOrderSurveyLinkGenerator::PORSLINE_ORDER_SURVEY_API, [
                   'json'    => [
                       'values' => [
                           ['identifier' => $orderIdentifier],
                       ],
                   ],
                   'headers' => [
                       'Content-Type'  => 'application/json',
                       'Authorization' => 'API-Key 123456',
                       'Accept'        => 'application/json',
                   ],
               ])
               ->andReturn($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to generate survey link. porsline api returned an unexpected response.');

        $linkGenerator = new PorslineOrderSurveyLinkGenerator($client, '123456');

        $linkGenerator->generateLink($orderIdentifier);
    }

    public function testItThrowExceptionIfPorslineResponseDoesNotContainUrls(): void
    {
        $orderIdentifier = 123456;

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')->once()->with(false)->andReturn('{"urls":[]}');

        $client = \Mockery::mock(HttpClientInterface::class);
        $client->shouldReceive('request')
               ->once()
               ->with('POST', PorslineOrderSurveyLinkGenerator::PORSLINE_ORDER_SURVEY_API, [
                   'json'    => [
                       'values' => [
                           ['identifier' => $orderIdentifier],
                       ],
                   ],
                   'headers' => [
                       'Content-Type'  => 'application/json',
                       'Authorization' => 'API-Key 123456',
                       'Accept'        => 'application/json',
                   ],
               ])
               ->andReturn($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to generate survey link. porsline api returned an unexpected response.');

        $linkGenerator = new PorslineOrderSurveyLinkGenerator($client, '123456');

        $linkGenerator->generateLink($orderIdentifier);
    }

    public function testItGenerateSurveyLink(): void
    {
        $orderIdentifier = 123456;

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')->once()->with(false)->andReturn('{"urls":["http://example.com"]}');

        $client = \Mockery::mock(HttpClientInterface::class);
        $client->shouldReceive('request')
               ->once()
               ->with('POST', PorslineOrderSurveyLinkGenerator::PORSLINE_ORDER_SURVEY_API, [
                   'json'    => [
                       'values' => [
                           ['identifier' => $orderIdentifier],
                       ],
                   ],
                   'headers' => [
                       'Content-Type'  => 'application/json',
                       'Authorization' => 'API-Key 123456',
                       'Accept'        => 'application/json',
                   ],
               ])
               ->andReturn($response);

        $linkGenerator = new PorslineOrderSurveyLinkGenerator($client, '123456');

        self::assertEquals('http://example.com', $linkGenerator->generateLink($orderIdentifier));
    }
}
