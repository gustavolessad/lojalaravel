<?php

namespace App\Listeners;

use App\Events\ReviewApproved;

class UpdateProductRating
{
    public function handle(ReviewApproved $event): void
    {
        $event->review->product->recalculateRating();
    }
}
