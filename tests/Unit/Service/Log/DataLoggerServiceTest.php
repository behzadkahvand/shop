<?php

namespace App\Tests\Unit\Service\Log;

use App\Document\InventoryLog;
use App\DTO\InventoryPriceHistoryData;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Service\Log\DataLoggerService;
use App\Tests\Unit\BaseUnitTestCase;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class DataLoggerServiceTest extends BaseUnitTestCase
{
    protected DocumentManager|LegacyMockInterface|MockInterface|null $documentManagerMock;

    protected ?dataLoggerService $dataLoggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentManagerMock = Mockery::mock(DocumentManager::class);
        $this->dataLoggerService   = new DataLoggerService($this->documentManagerMock);
    }

    public function testItCanCallLogInventoryPriceChange(): void
    {
        $option = Mockery::mock(ProductOption::class);
        $option->shouldReceive('getId')
               ->twice()
               ->andReturn(20, 30);

        $optionValue = Mockery::mock(ProductOptionValue::class);
        $optionValue->shouldReceive('getId')
                    ->twice()
                    ->andReturn(10, 20);
        $optionValue->shouldReceive('getCode')
                    ->twice()
                    ->andReturn("blue", null);
        $optionValue->shouldReceive('getValue')
                    ->twice()
                    ->andReturn(null, null);
        $optionValue->shouldReceive('getAttributes')
                    ->twice()
                    ->andReturn(null, ["key" => "value"]);
        $optionValue->shouldReceive('getOption')
                    ->twice()
                    ->andReturn($option, $option);

        $inventoryPriceHistoryData = (new InventoryPriceHistoryData())
            ->setInventoryId(1)
            ->setProductId(10)
            ->setProductTitle("product test")
            ->setSellerId(23)
            ->setSellerName("seller test")
            ->setProductVariantId(1)
            ->setPriceFrom(null)
            ->setPriceTo(150)
            ->setFinalPriceFrom(250)
            ->setFinalPriceTo(250)
            ->setIsActive(1)
            ->setUserId(20)
            ->setColor($optionValue)
            ->setGuarantee($optionValue)
            ->setSize(null)
            ->setIsInventoryBuyBox(true);

        $this->documentManagerMock->shouldReceive('persist')
                                  ->once()
                                  ->andReturn();

        $this->documentManagerMock->shouldReceive('flush')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn();

        $this->dataLoggerService->logInventoryPriceChange($inventoryPriceHistoryData);
    }

    /**
     * @dataProvider logProductSearchData
     */
    public function testItCanCallLogProductSearch(string $term, int $resultCount, ?int $customerId, ?DateTime $createdAt, bool $flashToDB): void
    {
        $this->documentManagerMock->shouldReceive('persist')
                                  ->once()
                                  ->andReturn();

        $this->documentManagerMock->shouldReceive('flush')
                                  ->withNoArgs()
                                  ->andReturn();

        $this->dataLoggerService->logProductSearch($term, $resultCount, $customerId, $createdAt, $flashToDB);
    }

    public function testItCanLogInventory(): void
    {
        $this->documentManagerMock->shouldReceive('persist')
                                  ->with(Mockery::type(InventoryLog::class))
                                  ->once()
                                  ->andReturn();

        $this->documentManagerMock->shouldReceive('flush')
                                  ->withNoArgs()
                                  ->andReturn();

        $this->dataLoggerService->logInventory([]);
    }

    public function logProductSearchData(): array
    {
        return [
            ["test", 10, 5, new DateTime("now"), false],
            ["test", 0, null, null, false],
            ["test", 0, null, null, true],
            ["test", 5, 1, new DateTime("now"), true],
        ];
    }
}
