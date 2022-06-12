<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaControllerTest
 */
final class MediaControllerTest extends BaseControllerTestCase
{
    public function testItThrowException()
    {
        $this->client->request('GET', '/media', [], [], [
            'HTTP_HOST' => 'image.timcheh.com',
        ]);

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
