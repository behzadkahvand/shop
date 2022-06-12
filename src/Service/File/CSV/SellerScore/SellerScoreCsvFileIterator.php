<?php

namespace App\Service\File\CSV\SellerScore;

use App\Service\File\CSV\BaseCSVIterator;
use App\Service\File\RowAbstract;

class SellerScoreCsvFileIterator extends BaseCSVIterator
{
    public function current(): RowAbstract
    {
        $row = $this->iterator->current();

        $sellerId = $row->getCells()[0]->getValue();
        $returnScore = $row->getCells()[1]->getValue();
        $deliveryDelayScore = $row->getCells()[2]->getValue();
        $orderCancellationScore = $row->getCells()[3]->getValue();
        $totalScore = $row->getCells()[4]->getValue();

        return new SellerScoreRow(
            $sellerId,
            $returnScore,
            $deliveryDelayScore,
            $orderCancellationScore,
            $totalScore
        );
    }
}
