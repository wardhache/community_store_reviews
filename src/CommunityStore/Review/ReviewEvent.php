<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review;

use Symfony\Component\EventDispatcher\GenericEvent;

class ReviewEvent extends GenericEvent
{
    protected $event;

    public function __construct($review, $newReview = null)
    {
        $this->review = $review;
        $this->newReview = $newReview;
    }

    public function getReview()
    {
        return $this->review;
    }

    public function getNewReview()
    {
        return $this->newReview;
    }
}
