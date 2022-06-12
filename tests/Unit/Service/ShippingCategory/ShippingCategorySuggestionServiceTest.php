<?php

namespace App\Tests\Unit\Service\ShippingCategory;

use App\Dictionary\ShippingCategoryName;
use App\DTO\Admin\ShippingCategorySuggestionData;
use App\Entity\ShippingCategory;
use App\Repository\ShippingCategoryRepository;
use App\Service\ShippingCategory\CalculateShippingCategoryNameService;
use App\Service\ShippingCategory\ShippingCategorySuggestionService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ShippingCategorySuggestionServiceTest extends MockeryTestCase
{
    protected ShippingCategorySuggestionService $shippingCategorySuggestion;

    private LegacyMockInterface|CalculateShippingCategoryNameService|MockInterface|null $calculateShippingCategoryNameMock;

    private LegacyMockInterface|MockInterface|ShippingCategoryRepository|null $shippingCategoryRepoMock;

    private ShippingCategory|LegacyMockInterface|MockInterface|null $shippingCategoryMock;

    private LegacyMockInterface|ShippingCategorySuggestionData|MockInterface|null $shippingCategorySuggestionDTOMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculateShippingCategoryNameMock = Mockery::mock(CalculateShippingCategoryNameService::class);

        $this->shippingCategoryRepoMock = Mockery::mock(ShippingCategoryRepository::class);

        $this->shippingCategoryMock = Mockery::mock(ShippingCategory::class);

        $this->shippingCategorySuggestionDTOMock = Mockery::mock(ShippingCategorySuggestionData::class);

        $this->shippingCategorySuggestion = new ShippingCategorySuggestionService(
            $this->calculateShippingCategoryNameMock,
            $this->shippingCategoryRepoMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->shippingCategorySuggestion);

        $this->calculateShippingCategoryNameMock = null;
        $this->shippingCategoryRepoMock = null;
        $this->shippingCategoryMock = null;
        $this->shippingCategorySuggestionDTOMock = null;
    }

    public function testItSuggestsShippingCategory()
    {
        $this->calculateShippingCategoryNameMock->shouldReceive('calculate')->once()->with(0.0505, 0.3, 0.2, 0.1)->andReturn(ShippingCategoryName::NORMAL);

        $this->shippingCategoryRepoMock->shouldReceive('findOneBy')->once()->with(['name' => ShippingCategoryName::NORMAL])->andReturn($this->shippingCategoryMock);

        $this->shippingCategorySuggestionDTOMock->shouldReceive('getWeight')->withNoArgs()->once()->andReturn(0.0505);
        $this->shippingCategorySuggestionDTOMock->shouldReceive('getLength')->withNoArgs()->once()->andReturn(0.3);
        $this->shippingCategorySuggestionDTOMock->shouldReceive('getWidth')->withNoArgs()->once()->andReturn(0.2);
        $this->shippingCategorySuggestionDTOMock->shouldReceive('getHeight')->withNoArgs()->once()->andReturn(0.1);

        $result = $this->shippingCategorySuggestion->get($this->shippingCategorySuggestionDTOMock);

        self::assertEquals($this->shippingCategoryMock, $result);
    }
}
