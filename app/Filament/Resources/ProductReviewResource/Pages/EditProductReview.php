<?php

namespace App\Filament\Resources\ProductReviewResource\Pages;

use App\Events\ReviewApproved;
use App\Filament\Resources\ProductReviewResource;
use Filament\Resources\Pages\EditRecord;

class EditProductReview extends EditRecord
{
    protected static string $resource = ProductReviewResource::class;

    protected function afterSave(): void
    {
        $review = $this->record;

        if ($review->wasChanged('status')) {
            if ($review->status === 'approved') {
                ReviewApproved::dispatch($review);
            } else {
                $review->product->recalculateRating();
            }
        }
    }
}
