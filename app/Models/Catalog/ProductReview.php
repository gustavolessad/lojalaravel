<?php

namespace App\Models\Catalog;

use App\Models\Customer\Customer;
use App\Models\Sales\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductReview extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'customer_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Aguardando moderação',
            'approved' => 'Aprovada',
            'rejected' => 'Recusada',
            default    => ucfirst($this->status),
        };
    }

    public static function statuses(): array
    {
        return [
            'pending'  => 'Aguardando moderação',
            'approved' => 'Aprovada',
            'rejected' => 'Recusada',
        ];
    }

    // ── Media Library ────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->fit(Fit::Contain, 400, 400)
            ->performOnCollections('photos')
            ->nonQueued();

        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(85)
            ->width(800)
            ->performOnCollections('photos')
            ->nonQueued();
    }
}
