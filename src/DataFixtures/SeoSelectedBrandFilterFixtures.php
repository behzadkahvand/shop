<?php

namespace App\DataFixtures;

use App\Entity\Seo\SeoSelectedBrandFilter;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeoSelectedBrandFilterFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'seo_selected_brand_1',
            $this->createSeoSelectedBrandFilter(
                'category_mobile',
                'brand_1',
                $this->faker->company(),
                $this->faker->company(),
                $this->faker->company(),
                false
            )
        );

        $this->setReferenceAndPersist(
            'seo_selected_brand_2',
            $this->createSeoSelectedBrandFilter(
                'category_room',
                'brand_2',
                $this->faker->company(),
                $this->faker->company(),
                $this->faker->company(),
                true
            )
        );

        $this->manager->flush();
    }

    private function createSeoSelectedBrandFilter(
        string $category,
        string $brand,
        string $title,
        string $description,
        string $metaDescription,
        bool $starred
    ): SeoSelectedBrandFilter {
        return (new SeoSelectedBrandFilter())->setCategory($this->getReference($category))
                                             ->setEntity($this->getReference($brand))
                                             ->setTitle($title)
                                             ->setDescription($description)
                                             ->setMetaDescription($metaDescription)
                                             ->setStarred($starred);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            BrandFixtures::class
        ];
    }
}
