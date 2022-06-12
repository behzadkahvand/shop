<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\RateAndReviewStatus;
use App\Entity\RateAndReview;
use App\Tests\Controller\BaseControllerTestCase;

class RateAndReviewControllerTest extends BaseControllerTestCase
{
    private ?RateAndReview $rateAndReview;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rateAndReview = $this->manager->getRepository(RateAndReview::class)->findOneBy([]);
    }

    public function testItCanListAllCustomerRateAndReviews(): void
    {
        $client = $this
            ->loginAs($this->customer)
            ->sendRequest('GET', '/reviews');

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $rateAndReviews = $response['results'];
        self::assertIsArray($rateAndReviews);

        foreach ($rateAndReviews as $rateAndReview) {
            self::assertArrayHasKey('id', $rateAndReview);
            self::assertArrayHasKey('title', $rateAndReview);

            self::assertArrayHasKey('body', $rateAndReview);
            self::assertNotEmpty($rateAndReview['body']);

            self::assertArrayHasKey('suggestion', $rateAndReview);
            self::assertNotEmpty($rateAndReview['suggestion']);

            self::assertArrayHasKey('rate', $rateAndReview);
            self::assertNotEmpty($rateAndReview['rate']);
            self::assertGreaterThanOrEqual(1, $rateAndReview['rate']);
            self::assertLessThanOrEqual(5, $rateAndReview['rate']);

            self::assertArrayHasKey('status', $rateAndReview);
            self::assertNotEmpty($rateAndReview['status']);
            self::assertContains($rateAndReview['status'], RateAndReviewStatus::toArray());

            self::assertArrayHasKey('product', $rateAndReview);
            if (null !== $rateAndReview['product']) {
                self::assertArrayHasKeys(['id', 'featuredImage'], $rateAndReview['product']);
                self::assertArrayHasKeys(['alt', 'path'], $rateAndReview['product']['featuredImage']);
            }

            self::assertArrayHasKey('productVariant', $rateAndReview);
            $variant = $rateAndReview['productVariant'];
            self::assertNotEmpty($variant);
            $options = $variant['options'];
            self::assertNotEmpty($options);
            foreach (['color', 'guarantee', 'otherOptions'] as $item) {
                self::assertArrayHasKey($item, $options);
                self::assertNotEmpty($item);
            }

            self::assertArrayHasKey('seller', $rateAndReview);
            self::assertNotEmpty($rateAndReview['seller']);
        }
    }

    public function testItCanListCustomerAllBoughtProductsWithNoRateAndReview(): void
    {
        $client = $this
            ->loginAs($this->customer)
            ->sendRequest('GET', '/reviews/products');

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $products = $response['results'];
        self::assertIsArray($products);

        foreach ($products as $product) {
            self::assertArrayHasKeys(['id', 'title', 'featuredImage'], $product);
            self::assertArrayHasKeys(['alt', 'path'], $product['featuredImage']);
            self::assertNotEmpty($product['title']);
        }
    }

    public function testItCanUpdateRateAndReviewAndStatusWillChangeToWaitForAccept(): void
    {
        $newBody = 'This is an updated body';

        $client = $this
            ->loginAs($this->customer)
            ->sendRequest(
                'PATCH',
                "/reviews/{$this->rateAndReview->getId()}",
                ['body' => $newBody]
            );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $rateAndReview = $response['results'];
        self::assertIsArray($rateAndReview);

        self::assertEquals($newBody, $rateAndReview['body']);
        self::assertEquals(RateAndReviewStatus::WAIT_FOR_ACCEPT, $rateAndReview['status']);
    }

    public function testItCanDeleteRateAndReview(): void
    {
        $id = $this->rateAndReview->getId();

        $client = $this
            ->loginAs($this->customer)
            ->sendRequest('DELETE', "/reviews/{$id}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $rateAndReview = $response['results'];
        self::assertIsArray($rateAndReview);

        self::assertEquals($id, $rateAndReview['id']);
    }
}
