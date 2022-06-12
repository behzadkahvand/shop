<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AttributeFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "attribute_integer",
            $this->createAttribute(
                'USB 3.1 count',
                "NUMERIC",
                false,
                null
            )
        );

        $this->setReferenceAndPersist(
            "attribute_boolean",
            $this->createAttribute(
                '3.5mm jack',
                "BOOLEAN",
                false,
                null
            )
        );

        $this->setReferenceAndPersist(
            "attribute_text_not_multiple",
            $this->createAttribute(
                'Charging',
                "TEXT",
                false,
                null
            )
        );

        $this->setReferenceAndPersist(
            "attribute_text_is_multiple",
            $this->createAttribute(
                'Other descriptions',
                "TEXT",
                true,
                null
            )
        );

        $this->setReferenceAndPersist(
            "attribute_list",
            $this->createAttribute(
                'Colors',
                "LIST",
                false,
                'attribute_list_colors'
            )
        );

        $this->setReferenceAndPersist(
            "attribute_not_assigned",
            $this->createAttribute(
                'Name',
                "TEXT",
                false,
                null
            )
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            AttributeListFixtures::class,
        ];
    }

    private function createAttribute(
        string $title,
        string $type,
        bool $isMultiple,
        ?string $attributeList
    ): Attribute {
        return (new Attribute())
            ->setTitle($title)
            ->setType($type)
            ->setIsMultiple($isMultiple)
            ->setList($attributeList ? $this->getReference($attributeList) : null);
    }
}
