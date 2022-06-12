<?php

namespace App\DataFixtures;

use App\Entity\CategoryAttributeGroup;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoryAttributeGroupFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("category_attribute_group_1", $this->createCategoryAttributeGroup(
            'category_mobile',
            'attribute_group_1',
            1
        ));

        $this->setReferenceAndPersist("category_attribute_group_2", $this->createCategoryAttributeGroup(
            'category_mobile',
            'attribute_group_2',
            2
        ));

        $this->setReferenceAndPersist("category_attribute_group_3", $this->createCategoryAttributeGroup(
            'category_mobile',
            'attribute_group_3',
            3
        ));

        $this->setReferenceAndPersist("category_attribute_group_4", $this->createCategoryAttributeGroup(
            'category_tv',
            'attribute_group_1',
            1
        ));

        $this->setReferenceAndPersist("category_attribute_group_5", $this->createCategoryAttributeGroup(
            'category_tv',
            'attribute_group_2',
            2
        ));

        $this->setReferenceAndPersist("category_attribute_group_6", $this->createCategoryAttributeGroup(
            'category_tv',
            'attribute_group_3',
            3
        ));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            AttributeGroupFixtures::class,
        ];
    }

    private function createCategoryAttributeGroup(
        string $category,
        string $attributeGroup,
        int $priority
    ): CategoryAttributeGroup {
        return (new CategoryAttributeGroup())
            ->setCategory($this->getReference($category))
            ->setAttributeGroup($this->getReference($attributeGroup))
            ->setPriority($priority);
    }
}
