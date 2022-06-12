<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;

class SearchSuggestionControllerTest extends BaseControllerTestCase
{
    public function testSearchSuggestionWhenSearchQueryNotExist(): void
    {
        $response = $this->sendRequest('GET', $this->router->generate('customer.search-suggestion.suggest'));
        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testSearchSuggestionWhenSearchQueryHasLessThan3Character(): void
    {
        $response = $this->sendRequest('GET', $this->router->generate('customer.search-suggestion.suggest', [
            'searchQuery' => 'دی',
        ]));
        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testSearchSuggestion(): void
    {
        $response = $this->sendRequest('GET', $this->router->generate('customer.search-suggestion.suggest', [
            'searchQuery' => 'hom',
        ]));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $categories = $this->getControllerResponse();

        self::assertIsArray($categories['results']);

        self::assertArrayHasKey('succeed', $categories);
        self::assertArrayHasKey('message', $categories);
        self::assertArrayHasKey('results', $categories);
        self::assertArrayHasKey('metas', $categories);

        self::assertTrue($categories['succeed']);

        $categories = $categories['results']['categories'];
        $category   = $categories[0];
        self::assertArrayHasKey('code', $category);
        self::assertArrayHasKey('title', $category);
    }
}
