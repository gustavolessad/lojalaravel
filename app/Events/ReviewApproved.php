<?php

namespace App\Events;

use App\Models\Catalog\ProductReview;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ProductReview $review,
    ) {}
}
