<?php

namespace App\DataFixtures;

use App\Entity\AttributeListItem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AttributeListItemFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("attribute_list_item_1", $this->createAttributeListItem(
            'Iran',
            'attribute_list_countries'
        ));

        $this->setReferenceAndPersist("attribute_list_item_2", $this->createAttributeListItem(
            'England',
            'attribute_list_countries'
        ));

        $this->setReferenceAndPersist("attribute_list_item_3", $this->createAttributeListItem(
            'China',
            'attribute_list_countries'
        ));

        $this->setReferenceAndPersist("attribute_list_item_4", $this->createAttributeListItem(
            'Red',
            'attribute_list_colors'
        ));

        $this->setReferenceAndPersist("attribute_list_item_5", $this->createAttributeListItem(
            'Green',
            'attribute_list_colors'
        ));

        $this->setReferenceAndPersist("attribute_list_item_6", $this->createAttributeListItem(
            'Blue',
            'attribute_list_colors'
        ));

        $this->setReferenceAndPersist("attribute_list_item_7", $this->createAttributeListItem(
            '3G',
            'attribute_list_networks'
        ));

        $this->setReferenceAndPersist("attribute_list_item_8", $this->createAttributeListItem(
            '4G',
            'attribute_list_networks'
        ));

        $this->setReferenceAndPersist("attribute_list_item_9", $this->createAttributeListItem(
            '5G',
            'attribute_list_networks'
        ));

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

    private function createAttributeListItem(string $title, string $list): AttributeListItem
    {
        return (new AttributeListItem())
            ->setTitle($title)
            ->setList($this->getReference($list));
    }
}
