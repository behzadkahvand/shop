<?php

namespace App\DataFixtures;

use App\Entity\AttributeGroup;

class AttributeGroupFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("attribute_group_1", $this->createAttributeGroup(
            'display'
        ));

        $this->setReferenceAndPersist("attribute_group_2", $this->createAttributeGroup(
            'features'
        ));

        $this->setReferenceAndPersist("attribute_group_3", $this->createAttributeGroup(
            'platform'
        ));

        $this->setReferenceAndPersist("attribute_group_4", $this->createAttributeGroup(
            'camera'
        ));

        $this->manager->flush();
    }

    private function createAttributeGroup(string $title): AttributeGroup
    {
        return (new AttributeGroup())->setTitle($title);
    }
}
