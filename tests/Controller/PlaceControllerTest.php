<?php

namespace App\Tests\Controller;

/**
 * Class PlaceControllerTest
 */
final class PlaceControllerTest extends BaseControllerTestCase
{
    public function testShowProvinces()
    {
        $client   = $this->sendRequest('GET', '/places/provinces');
        $response = $client->getResponse();

        self::assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent(), true);

        self::assertArrayHasKey('succeed', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('results', $result);
        self::assertArrayHasKey('metas', $result);

        self::assertIsArray($result['results']);

        foreach ($result['results'] as $province) {
            self::assertArrayHasKey('id', $province);
            self::assertArrayHasKey('code', $province);
            self::assertArrayHasKey('name', $province);
        }
    }

    public function testShowCities()
    {
        $client   = $this->sendRequest('GET', '/places/cities');
        $response = $client->getResponse();

        self::assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent(), true);

        self::assertArrayHasKey('succeed', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('results', $result);
        self::assertArrayHasKey('metas', $result);

        self::assertIsArray($result['results']);

        foreach ($result['results'] as $province) {
            self::assertArrayHasKey('id', $province);
            self::assertArrayHasKey('name', $province);
        }
    }

    public function testShowDistricts()
    {
        $client   = $this->sendRequest('GET', '/places/districts');
        $response = $client->getResponse();

        self::assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent(), true);

        self::assertArrayHasKey('succeed', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('results', $result);
        self::assertArrayHasKey('metas', $result);

        self::assertIsArray($result['results']);

        foreach ($result['results'] as $province) {
            self::assertArrayHasKey('id', $province);
            self::assertArrayHasKey('name', $province);
        }
    }
}
