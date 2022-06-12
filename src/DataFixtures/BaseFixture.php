<?php

namespace App\DataFixtures;

use App\Faker\Provider\CustomImageProvider;
use App\Faker\Provider\DateTimeProvider;
use App\Faker\Provider\PasswordProvider;
use App\Faker\Provider\PointProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class BaseFixture extends Fixture
{
    protected ObjectManager $manager;

    protected Generator $faker;

    public function __construct(protected UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker   = Factory::create();

        $this->faker->addProvider(new CustomImageProvider($this->faker));
        $this->faker->addProvider(new DateTimeProvider($this->faker));
        $this->faker->addProvider(new PasswordProvider($this->faker, $this->hasher));
        $this->faker->addProvider(new PointProvider($this->faker));

        $this->loadData();
    }

    protected function createMany(string $className, int $count, callable $factory, bool $addReference = false): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);
            $this->manager->persist($entity);
            if ($addReference) {
                $this->addReference(snake_case(class_basename($entity)) . '_' . $i, $entity);
            }
        }
    }

    protected function setReferenceAndPersist(
        string $referenceName,
        object $object
    ): void {
        $this->addReference($referenceName, $object);
        $this->manager->persist($object);
    }

    abstract protected function loadData(): void;
}
