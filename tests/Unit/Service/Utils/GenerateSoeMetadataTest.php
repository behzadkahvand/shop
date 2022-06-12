<?php

namespace App\Tests\Unit\Service\Utils;

use App\Service\Utils\GenerateSoeMetadata;
use App\Tests\Unit\BaseUnitTestCase;
use Faker\Factory;

class GenerateSoeMetadataTest extends BaseUnitTestCase
{
    private ?string $brandName;
    private ?string $categoryName;

    protected function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();
        $this->categoryName = $faker->title();
        $this->brandName = $faker->title();
    }

    public function testItReturnTitle(): void
    {
        $title = (new GenerateSoeMetadata())->title($this->categoryName, $this->brandName);
        $this->assertEquals(
            sprintf(
                "%s %s %s %s",
                GenerateSoeMetadata::TITLE_PREFIX,
                $this->categoryName,
                $this->brandName,
                GenerateSoeMetadata::TITLE_POSTFIX
            ),
            $title
        );
    }

    public function testItReturnTitleWhenOnlyPassCategory(): void
    {
        $title = (new GenerateSoeMetadata())->title($this->categoryName);
        $this->assertEquals(
            sprintf(
                "%s %s %s",
                GenerateSoeMetadata::TITLE_PREFIX,
                $this->categoryName,
                GenerateSoeMetadata::TITLE_POSTFIX
            ),
            $title
        );
    }

    public function testItReturnMetaDescription(): void
    {
        $title = (new GenerateSoeMetadata())->metaDescription($this->categoryName, $this->brandName);
        $this->assertEquals(
            sprintf(
                "%s %s %s %s",
                GenerateSoeMetadata::META_DESCRIPTION_PREFIX,
                $this->categoryName,
                $this->brandName,
                GenerateSoeMetadata::META_DESCRIPTION_POSTFIX
            ),
            $title
        );
    }

    public function testItReturnMetaDescriptionWhenOnlyPassCategory(): void
    {
        $title = (new GenerateSoeMetadata())->metaDescription($this->categoryName);
        $this->assertEquals(
            sprintf(
                "%s %s %s",
                GenerateSoeMetadata::META_DESCRIPTION_PREFIX,
                $this->categoryName,
                GenerateSoeMetadata::META_DESCRIPTION_POSTFIX
            ),
            $title
        );
    }
}
