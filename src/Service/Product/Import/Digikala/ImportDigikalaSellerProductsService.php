<?php

namespace App\Service\Product\Import\Digikala;

use App\Messaging\Messages\Command\Product\BatchImportDigikalaProducts;
use App\Service\Digikala\DigikalaSellerPageLink;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportDigikalaSellerProductsService
{
    public function __construct(
        protected HttpClientInterface $client,
        protected MessageBusInterface $bus
    ) {
    }

    public function import(int $sellerId, string $digikalaSellerId): void
    {
        $response = $this->client->request('GET', DigikalaSellerPageLink::generate($digikalaSellerId))->getContent();
        $sellerPageData = json_decode($response, true)['data'];

        $pagesCount = $sellerPageData['pager']['total_pages'];

        for ($page = 1; $page <= $pagesCount; $page++) {
            $this->importProducts($sellerId, $digikalaSellerId, $page);
        }
    }

    private function importProducts(int $sellerId, string $digikalaSellerId, int $pageNo): void
    {
        $this->bus->dispatch(
            new BatchImportDigikalaProducts(
                DigikalaSellerPageLink::generate($digikalaSellerId, $pageNo),
                $sellerId,
                $digikalaSellerId
            )
        );
    }
}
