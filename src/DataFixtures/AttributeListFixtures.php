<?php

namespace App\DataFixtures;

use App\Entity\AttributeList;

class AttributeListFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("attribute_list_countries", $this->createAttributeList(
            'countries'
        ));

        $this->setReferenceAndPersist("attribute_list_colors", $this->createAttributeList(
            'colors'
        ));

        $this->setReferenceAndPersist("attribute_list_networks", $this->createAttributeList(
            'networks'
        ));

        $this->manager->flush();
    }

    private function createAttributeList(string $title): AttributeList
    {
        return (new AttributeList())->setTitle($title);
    }
}
