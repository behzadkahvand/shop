<?php

namespace App\DataFixtures;

use App\Entity\Admin;

class AdminFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $admin = new Admin();
        $admin->setName('nimda');
        $admin->setFamily('nimda');
        $admin->setEmail('nimda@timcheh.ir');
        $admin->setIsActive(1);
        $admin->setMobile('09123456700');
        $admin->setPassword($this->faker->encodePassword($admin, '123456'));

        $this->addReference('admin_1', $admin);

        $this->manager->persist($admin);
        $this->manager->flush();
    }
}
