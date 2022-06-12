<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Seller;
use App\Tests\Traits\DataFixturesTrait;
use Doctrine\DBAL\ConnectionException;
use Faker\Factory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaseControllerTestCase extends FunctionalTestCase
{
    use FixturesTrait;
    use DataFixturesTrait;
    use MakesHttpRequests;

    private static bool $fixturesLoaded = false;

    protected $manager;

    protected $faker;

    protected $router;

    protected $client;

    protected $dispatcher;

    protected ?Seller $seller;

    protected ?Customer $customer;

    protected ?Admin $admin;

    private $jwtToken;

    private bool $shouldLogin = false;

    protected static function assertArrayHasKeys(array $keys, array $subject): void
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $subject);
        }
    }

    protected static function assertArrayHasKeysAndValues(array $keysAndValues, array $subject): void
    {
        foreach ($keysAndValues as $key => $value) {
            self::assertArrayHasKey($key, $subject);
            self::assertEquals($value, $subject[$key]);
        }
    }

    protected function setUp(): void
    {
        $this->client     = self::createClient();
        $this->faker      = Factory::create();
        $this->dispatcher = $this->client->getContainer()->get('event_dispatcher');
        $this->manager    = $this->client->getContainer()->get('doctrine')->getManager();
        $this->router     = $this->client->getContainer()->get('router');
        $loadFixtures     = $this->client->getContainer()->getParameter('app.load_fixtures');

        if ($loadFixtures && !self::$fixturesLoaded) {
            $this->loadFixtures(classNames: $this->getFixtures());

            $this->manager->clear();

            self::$fixturesLoaded = true;
        }

        $this->seller   = $this->manager->getRepository(Seller::class)->findOneBy([]);
        $this->customer = $this->manager->getRepository(Customer::class)->findOneBy([]);
        $this->admin    = $this->manager->getRepository(Admin::class)->findOneBy([]);

        $this->manager->getConfiguration()->setSQLLogger(null);
        $this->manager->getConnection()->beginTransaction();

        $this->client->getContainer()->set(
            HttpClientInterface::class,
            new MockHttpClient(new MockResponse())
        );
    }

    /**
     * @throws ConnectionException
     */
    protected function tearDown(): void
    {
        $this->manager->clear();

        if ($this->manager->getConnection()->isTransactionActive()) {
            $this->manager->getConnection()->rollBack();
        }

        $this->client     = null;
        $this->faker      = null;
        $this->dispatcher = null;
        $this->manager    = null;
        $this->router     = null;
        $this->customer   = null;
        $this->admin      = null;
        $this->seller     = null;
        $this->jwtToken   = null;

        parent::tearDown();
    }

    /**
     * @return array
     * @throws \JsonException
     */
    protected function getControllerResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function route(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters);
    }

    protected function getService($id)
    {
        return $this->client->getContainer()->get($id);
    }

    protected function loginAs(?UserInterface $user): self
    {
        if ($user) {
            $this->jwtToken    = $this->client->getContainer()->get(JWTTokenManagerInterface::class)->create($user);
            $this->shouldLogin = true;
        } else {
            $this->shouldLogin = false;
        }

        return $this;
    }

    protected function logout(): self
    {
        $this->shouldLogin = false;
        $this->jwtToken    = null;

        return $this;
    }

    protected function entityCount($entityClass): int
    {
        $count = $this
            ->manager
            ->getRepository($entityClass)
            ->createQueryBuilder('entity')
            ->select('count(entity.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count;
    }

    protected function lastEntity($entityClass): object
    {
        return $this
            ->manager
            ->getRepository($entityClass)
            ->findOneBy([], ['id' => 'desc']);
    }

    private function getToken(string $uri): ?string
    {
        $class = strpos($uri, '/admin') === 0 ? Admin::class : Customer::class;
        $user  = $this->manager->getRepository($class)->findOneBy([]);

        return $this->jwtToken = $user ? $this->client->getContainer()->get(JWTTokenManagerInterface::class)->create($user) : null;
    }

    private function shouldLogin(): bool
    {
        return $this->shouldLogin;
    }

    public static function assertExceptionResponseEnvelope(array $response): void
    {
        self::assertArrayHasKeys(['title', 'status', 'detail'], $response);
    }

    protected function assertSuccessResponseKeys(): void
    {
        $response = $this->getControllerResponse();

        self::assertArrayHasKeys(['succeed', 'message', 'results', 'metas'], $response);
    }
}
