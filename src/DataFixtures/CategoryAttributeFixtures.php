<?php

namespace App\DataFixtures;

use App\Entity\CategoryAttribute;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoryAttributeFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("category_attribute_1", $this->createCategoryAttribute(
            'category_mobile',
            'attribute_group_1',
            'attribute_boolean',
            true,
            false,
            1
        ));

        $this->setReferenceAndPersist("category_attribute_2", $this->createCategoryAttribute(
            'category_mobile',
            'attribute_group_2',
            'attribute_integer',
            true,
            false,
            2
        ));

        $this->setReferenceAndPersist("category_attribute_3", $this->createCategoryAttribute(
            'category_mobile',
            'attribute_group_3',
            'attribute_text_not_multiple',
            true,
            false,
            3
        ));

        $this->setReferenceAndPersist("category_attribute_4", $this->createCategoryAttribute(
            'category_mobile',
            'attribute_group_1',
            'attribute_list',
            true,
            false,
            4
        ));

        $this->setReferenceAndPersist("category_attribute_5", $this->createCategoryAttribute(
            'category_tv',
            'attribute_group_1',
            'attribute_text_is_multiple',
            false,
            false,
            1
        ));

        $this->setReferenceAndPersist("category_attribute_6", $this->createCategoryAttribute(
            'category_tv',
            'attribute_group_2',
            'attribute_list',
            false,
            false,
            2
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
            AttributeFixtures::class,
            AttributeGroupFixtures::class,
        ];
    }

    private function createCategoryAttribute(
        string $category,
        string $attributeGroup,
        string $attribute,
        bool $isRequired,
        bool $isFilter,
        int $priority
    ): CategoryAttribute {
        return (new CategoryAttribute())
            ->setCategory($this->getReference($category))
            ->setAttributeGroup($this->getReference($attributeGroup))
            ->setAttribute($this->getReference($attribute))
            ->setIsRequired($isRequired)
            ->setIsFilter($isFilter)
            ->setPriority($priority);
    }
}
