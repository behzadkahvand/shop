<?php

namespace App\Tests\Controller\Seller;

use App\Tests\Controller\BaseControllerTestCase;

class DashboardControllerTest extends BaseControllerTestCase
{
    public function testItCanGetSellerDashboardData()
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.dashboard.index'),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $results = $response['results'];

        self::assertArrayHasKey('seller', $results);
        self::assertArrayHasKey('orderItems', $results);
        self::assertArrayHasKey('inventories', $results);
        self::assertArrayHasKey('soldItems', $results);

        $seller = $results['seller'];
        self::assertArrayHasKey('name', $seller);
        self::assertArrayHasKey('userName', $seller);

        $orderItems = $results['orderItems'];
        self::assertArrayHasKey('waitingForSend', $orderItems);
        self::assertArrayHasKey('delayed', $orderItems);
        self::assertArrayHasKey('sent', $orderItems);
        self::assertArrayHasKey('futureWaitingForSend', $orderItems);
        self::assertArrayHasKey('todayWaitingForSend', $orderItems);

        $inventories = $results['inventories'];
        self::assertArrayHasKey('active', $inventories);
        self::assertArrayHasKey('inactive', $inventories);
        self::assertArrayHasKey('withoutStock', $inventories);
        self::assertArrayHasKey('buyBox', $inventories);

        $soldItems = $results['soldItems'];
        self::assertArrayHasKey('lastSevenDays', $soldItems);
        self::assertArrayHasKey('count', $soldItems['lastSevenDays']);
        self::assertArrayHasKey('total', $soldItems['lastSevenDays']);
        self::assertArrayHasKey('lastThirtyDays', $soldItems);
        self::assertArrayHasKey('count', $soldItems['lastThirtyDays']);
        self::assertArrayHasKey('total', $soldItems['lastThirtyDays']);
        self::assertArrayHasKey('all', $soldItems);
        self::assertArrayHasKey('count', $soldItems['all']);
        self::assertArrayHasKey('total', $soldItems['all']);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);
    }
}
