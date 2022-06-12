<?php

namespace App\Tests\Integration;

use App\Tests\Controller\FunctionalTestCase;
use App\Tests\Traits\DataFixturesTrait;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class BaseIntegrationTestCase extends FunctionalTestCase
{
    use FixturesTrait;
    use DataFixturesTrait;

    private static bool $fixturesLoaded = false;

    protected ?EntityManager $manager;

    protected ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client  = self::createClient();
        $this->manager = $this->getService('doctrine')->getManager();
        $this->createDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->manager->clear();

        if ($this->manager->getConnection()->isTransactionActive()) {
            $this->manager->getConnection()->rollBack();
        }

        $this->manager = null;
        $this->client  = null;

        $props = $this->getCurrentTestClassProperties();
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $prop->setValue($this, null);
        }
    }

    private function createDatabase()
    {
        $loadFixtures = $this->client->getContainer()->getParameter('app.load_fixtures');

        if ($loadFixtures && !self::$fixturesLoaded) {
            $this->loadFixtures(classNames: $this->getFixtures());

            $this->manager->clear();

            self::$fixturesLoaded = true;
        }
    }

    protected function getService($id): ?object
    {
        return $this->client->getContainer()->get($id);
    }

    protected function truncateEntities(array $entities): void
    {
        $connection = $this->manager->getConnection();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $databasePlatform = $connection->getDatabasePlatform();
        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->manager->getClassMetadata($entity)->getTableName()
            );
            $connection->executeStatement($query);
        }
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

    private function getCurrentTestClassProperties(): array
    {
        $testClassName = get_class($this);
        $class         = new ReflectionClass($testClassName);

        return array_filter(
            $class->getProperties(),
            function ($property) use ($testClassName) {
                return $property->class === $testClassName;
            }
        );
    }
}
