<?php

namespace App\Service\RateAndReview\Events;

use App\Entity\RateAndReview;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class RateAndReviewAccepted
 */
final class RateAndReviewAccepted extends Event
{
    /**
     * @var RateAndReview
     */
    private RateAndReview $review;

    /**
     * RateAndReviewAccepted constructor.
     *
     * @param RateAndReview $review
     */
    public function __construct(RateAndReview $review)
    {
        $this->review = $review;
    }

    /**
     * @return RateAndReview
     */
    public function getReview(): RateAndReview
    {
        return $this->review;
    }
}
