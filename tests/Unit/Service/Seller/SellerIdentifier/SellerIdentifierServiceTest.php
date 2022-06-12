<?php

namespace App\Tests\Unit\Service\Seller\SellerIdentifier;

use App\Service\Seller\SellerIdentifier\SellerIdentifierService;
use App\Tests\Controller\FunctionalTestCase;

class SellerIdentifierServiceTest extends FunctionalTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testItCanGenerateSellerIdentifier(int $id, string $identifier)
    {
        $sellerIdentifier = new SellerIdentifierService();

        $result = $sellerIdentifier->generate($id);

        self::assertEquals($identifier, $result);
    }

    public function dataProvider()
    {
        return [
            [1, 'q7egp'],
            [5, 'vwzlp'],
            [10, '0789w'],
            [15, 'kp54w'],
            [20, '6plrp'],
        ];
    }
}
