<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Log;

use App\Document\SellerScoreLog;
use App\Entity\SellerScore;
use App\Messaging\Handlers\Command\Log\SellerScoreUpdateMessageHandler;
use App\Messaging\Messages\Command\Log\SellerScoreUpdateMessage;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;

class SellerScoreUpdateMessageHandlerTest extends BaseUnitTestCase
{
    public function testShouldPersistLog(): void
    {
        $sellerId = 2;
        $score    = Mockery::mock(SellerScore::class);
        $score->shouldReceive('getTotalScore')->once()->withNoArgs()->andReturn(34);
        $score->shouldReceive('getReturnScore')->once()->withNoArgs()->andReturn(34);
        $score->shouldReceive('getDeliveryDelayScore')->once()->withNoArgs()->andReturn(34);
        $score->shouldReceive('getOrderCancellationScore')->once()->withNoArgs()->andReturn(34);

        $manager = Mockery::mock(DocumentManager::class);

        $message = new SellerScoreUpdateMessage($sellerId, $score);
        $sut     = new SellerScoreUpdateMessageHandler($manager);

        $manager->shouldReceive('persist')->once()->with(SellerScoreLog::class)->andReturnNull();
        $manager->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();

        $sut->__invoke($message);
    }
}
