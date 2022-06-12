<?php

namespace App\Tests\Unit\EventSubscriber\Auth;

use App\Events\Auth\UserCredentialsChanged;
use App\Events\Auth\UserDeactivated;
use App\EventSubscriber\Auth\RevokeRefreshTokenSubscriber;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RevokeRefreshTokenSubscriberTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|RefreshTokenManagerInterface|null $refreshTokenManager;
    private LegacyMockInterface|RefreshTokenRepository|MockInterface|null $repo;
    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;
    private LegacyMockInterface|MockInterface|UserInterface|null $user;
    private RefreshToken|LegacyMockInterface|MockInterface|null $refreshToken_1;
    private RefreshToken|LegacyMockInterface|MockInterface|null $refreshToken_2;
    private RevokeRefreshTokenSubscriber|null $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTokenManager = Mockery::mock(RefreshTokenManagerInterface::class);
        $this->em = Mockery::mock(EntityManagerInterface::class);
        $this->repo = Mockery::mock(RefreshTokenRepository::class);
        $this->user = Mockery::mock(UserInterface::class);
        $this->refreshToken_1 = Mockery::mock(RefreshToken::class);
        $this->refreshToken_2 = Mockery::mock(RefreshToken::class);

        $this->sut = new RevokeRefreshTokenSubscriber($this->refreshTokenManager, $this->em);
    }

    public function testOnCredentialsChangeShouldDeleteAllUserRefreshTokens(): void
    {
        $username = 'dummy-username';

        $this->user->expects('getUserIdentifier')->withNoArgs()->andReturn($username);
        $this->em->expects('getRepository')->with(RefreshToken::class)->andReturn($this->repo);
        $this->repo
            ->expects('findBy')
            ->with(['username' => $username])
            ->andReturn(new ArrayCollection([$this->refreshToken_1, $this->refreshToken_2]));
        $this->refreshTokenManager->expects('delete')->with($this->refreshToken_1, false)->andReturnNull();
        $this->refreshTokenManager->expects('delete')->with($this->refreshToken_2, false)->andReturnNull();
        $this->em->expects('flush')->withNoArgs()->andReturnNull();

        $event = new UserCredentialsChanged($this->user);

        $this->sut->onCredentialsChange($event);
    }

    public function testOnUserDeactivatedShouldDeleteAllUserRefreshTokens(): void
    {
        $username = 'dummy-username';

        $this->user->expects('getUserIdentifier')->withNoArgs()->andReturn($username);
        $this->em->expects('getRepository')->with(RefreshToken::class)->andReturn($this->repo);
        $this->repo->expects('findBy')
            ->with(['username' => $username])
            ->andReturn(new ArrayCollection([$this->refreshToken_1, $this->refreshToken_2]));
        $this->refreshTokenManager->expects('delete')->with($this->refreshToken_1, false)->andReturnNull();
        $this->refreshTokenManager->expects('delete')->with($this->refreshToken_2, false)->andReturnNull();
        $this->em->expects('flush')->withNoArgs()->andReturnNull();

        $event = new UserDeactivated($this->user);

        $this->sut->onUserDeactivated($event);
    }

    public function testSubscribedEvents(): void
    {
        self::assertEquals(
            [
                UserCredentialsChanged::class => 'onCredentialsChange',
                UserDeactivated::class => 'onUserDeactivated'
            ],
            RevokeRefreshTokenSubscriber::getSubscribedEvents()
        );
    }
}
