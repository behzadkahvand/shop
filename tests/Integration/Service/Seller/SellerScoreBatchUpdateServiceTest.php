<?php

namespace App\Tests\Integration\Service\Seller;

use App\Entity\Seller;
use App\Service\Seller\SellerScore\SellerScoreBatchUpdateService;
use App\Tests\Integration\BaseIntegrationTestCase;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Bundle\MakerBundle\Str;

class SellerScoreBatchUpdateServiceTest extends BaseIntegrationTestCase
{
    private ?Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->manager->getRepository(Seller::class)->findOneBy([]);
    }

    public function testShouldReadExcelFileAndUpdateSellerScore(): void
    {
        $returnScore = 50;
        $deliveryDelayScore = 41;
        $orderCancellationScore = 25;
        $totalScore = 120;
        $row = [
                $this->seller->getId(),
                $returnScore,
                $deliveryDelayScore,
                $orderCancellationScore,
                $totalScore
            ];
        $filePath = $this->createTempExcelFileWith($row);

        $sut = $this->client->getContainer()->get(SellerScoreBatchUpdateService::class);

        $sut->execute($filePath);

        $sellerScore = $this->seller->getScore();

        self::assertEquals($returnScore, $sellerScore->getReturnScore());
        self::assertEquals($deliveryDelayScore, $sellerScore->getDeliveryDelayScore());
        self::assertEquals($orderCancellationScore, $sellerScore->getOrderCancellationScore());
        self::assertEquals($totalScore, $sellerScore->getTotalScore());
    }

    private function createTempExcelFileWith(array ...$rows)
    {
        $fileName = Str::getRandomTerm();
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName . '.xlsx';
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $headerRow = WriterEntityFactory::createRowFromArray(
            [
                'Seller ID', 'Return rate', 'Delayed delivery rate', 'Order cancellation rate', 'Total point'
            ]
        );
        $writer->addRow($headerRow);

        foreach ($rows as $row) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        }

        $writer->close();

        return $filePath;
    }
}
