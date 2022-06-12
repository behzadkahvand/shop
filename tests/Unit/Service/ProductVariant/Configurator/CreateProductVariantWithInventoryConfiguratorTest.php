<?php

namespace App\Tests\Unit\Service\ProductVariant\Configurator;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Service\Configuration\ConfigurationService;
use App\Service\ProductVariant\Configurator\CreateProductVariantWithInventoryConfigurator;
use App\Service\ProductVariant\CreateProductVariantWithInventoryService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateProductVariantWithInventoryConfiguratorTest extends MockeryTestCase
{
    /**
     * @var ConfigurationService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationServiceMock;

    /**
     * @var Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationMock;

    /**
     * @var CreateProductVariantWithInventoryService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $createProductVariantWithInventoryMock;

    protected ?CreateProductVariantWithInventoryConfigurator $configurator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationServiceMock              = Mockery::mock(ConfigurationService::class);
        $this->configurationMock                     = Mockery::mock(Configuration::class);
        $this->createProductVariantWithInventoryMock = Mockery::mock(CreateProductVariantWithInventoryService::class);

        $this->configurator = new CreateProductVariantWithInventoryConfigurator($this->configurationServiceMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->configurator                          = null;
        $this->configurationServiceMock              = null;
        $this->configurationMock                     = null;
        $this->createProductVariantWithInventoryMock = null;
    }

    public function testItCanSetCheckInitialInventoryStatusWhenConfigurationNotFound()
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturnNull();

        $this->createProductVariantWithInventoryMock->shouldReceive('setCheckInitialStatus')
                                                    ->once()
                                                    ->with(false)
                                                    ->andReturn();

        $this->configurator->configure($this->createProductVariantWithInventoryMock);
    }

    public function testItCanSetCheckInitialInventoryStatusWhenConfigurationHasFalseValue()
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnFalse();

        $this->createProductVariantWithInventoryMock->shouldReceive('setCheckInitialStatus')
                                                    ->once()
                                                    ->with(false)
                                                    ->andReturn();

        $this->configurator->configure($this->createProductVariantWithInventoryMock);
    }

    public function testItCanSetCheckInitialInventoryStatusWhenConfigurationHasTrueValue()
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->createProductVariantWithInventoryMock->shouldReceive('setCheckInitialStatus')
                                                    ->once()
                                                    ->with(true)
                                                    ->andReturn();

        $this->configurator->configure($this->createProductVariantWithInventoryMock);
    }
}
