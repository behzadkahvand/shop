<?php

namespace App\Service\Product\Import\Digikala;

use App\Messaging\Messages\Command\Product\ImportProductFromDigikala;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BatchImportDigikalaProductsService
{
    public function __construct(
        protected HttpClientInterface $client,
        protected MessageBusInterface $bus,
    ) {
    }

    public function import(string $pageUrl, ?int $sellerId = null, ?string $digikalaSellerId = null): void
    {
        $response = $this->client->request('GET', $pageUrl)->getContent();
        $pageData = json_decode($response, true)['data'];

        $digikalaProductIds = $this->fetchDigikalaProductIdsFrom($pageData);

        foreach ($digikalaProductIds as $dkp) {
            $this->bus->dispatch(new ImportProductFromDigikala($dkp, $sellerId, $digikalaSellerId));
        }
    }

    private function fetchDigikalaProductIdsFrom(array $pageData): array
    {
        return array_map(
            fn(array $product): string => $product['id'],
            $pageData['products']
        );
    }
}
