<?php

namespace App\Tests\Unit\Service\ExceptionHandler\Configurator;

use App\Service\ExceptionHandler\Configurator\StaticListMetadataLoaderConfigurator;
use App\Service\ExceptionHandler\Loaders\StaticListMetadataLoader;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class StaticListMetadataLoaderConfiguratorTest
 */
final class StaticListMetadataLoaderConfiguratorTest extends MockeryTestCase
{
    public function testItConfigureStaticListMetadataLoader()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'static_list_metadata.php';
        file_put_contents($tempFile, '<?php return [];');

        $loader = \Mockery::mock(StaticListMetadataLoader::class);
        $loader->shouldReceive('setFactories')->once()->with([])->andReturn();

        $configurator = new StaticListMetadataLoaderConfigurator($tempFile);
        $configurator->configure($loader);

        @unlink($tempFile);
    }
}
