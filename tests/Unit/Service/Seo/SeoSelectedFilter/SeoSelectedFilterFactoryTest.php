<?php

namespace App\Tests\Unit\Service\Seo\SeoSelectedFilter;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Service\Seo\SeoSelectedFilter\Exceptions\InvalidSeoSelectedEntityException;
use App\Service\Seo\SeoSelectedFilter\SeoSelectedFilterFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SeoSelectedFilterFactoryTest extends MockeryTestCase
{
    protected ?SeoSelectedFilterFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new SeoSelectedFilterFactory();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->factory = null;
    }

    public function testItCanGetSeoSelectedBrandFilter(): void
    {
        $result = $this->factory->getSeoSelectedFilter(new Brand());

        self::assertInstanceOf(SeoSelectedBrandFilter::class, $result);
    }

    public function testItThrowsExceptionWhenEntityIsInvalid(): void
    {
        $this->expectException(InvalidSeoSelectedEntityException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Seo selected entity is invalid!');

        $this->factory->getSeoSelectedFilter(new Category());
    }
}
