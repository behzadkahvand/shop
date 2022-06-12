<?php

namespace App\Service\Product\Update;

use App\Entity\Product;
use App\Exceptions\Product\Import\ProductImportException;
use App\Service\Digikala\DigikalaProductLink;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OutsourceProductUpdateService
{
    public function __construct(protected HttpClientInterface $client)
    {
    }

    /**
     * @throws ProductImportException
     */
    public function update(Product $product, PropertyUpdater ...$updaters): void
    {
        $response = $this->client->request('GET', DigikalaProductLink::generate($product->getDigikalaDkp()))->getContent();
        $dkp = json_decode($response, true)['data'];

        if (isset($dkp['product']['is_inactive']) && $dkp['product']['is_inactive']) {
            throw new ProductImportException('Product is inactive in digikala');
        }

        foreach ($updaters as $updater) {
            $updater->update($product, $dkp);
        }
    }
}
