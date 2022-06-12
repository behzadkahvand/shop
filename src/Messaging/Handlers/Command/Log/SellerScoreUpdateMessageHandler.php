<?php

namespace App\Messaging\Handlers\Command\Log;

use App\Document\SellerScoreLog;
use App\Messaging\Messages\Command\Log\SellerScoreUpdateMessage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SellerScoreUpdateMessageHandler implements MessageHandlerInterface
{
    public function __construct(protected DocumentManager $manager)
    {
    }

    public function __invoke(SellerScoreUpdateMessage $message): void
    {
        $sellerScore = $message->getSellerScore();

        $log = new SellerScoreLog();
        $log->setSellerId($message->getSellerId())
            ->setReturnScore($sellerScore->getReturnScore())
            ->setDeliveryDelayScore($sellerScore->getDeliveryDelayScore())
            ->setOrderCancellationScore($sellerScore->getOrderCancellationScore())
            ->setTotalScore($sellerScore->getTotalScore());

        $this->manager->persist($log);
        $this->manager->flush();
    }
}
