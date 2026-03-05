<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttributeValue extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'display_value',
        'order',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->value ?: 'valor');
            }
        });
    }

    protected $attributes = [
        'order' => 0,
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_values');
    }

    public function getLabel(): string
    {
        return $this->display_value ?? $this->value;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->fit(Fit::Contain, 100, 100)
            ->performOnCollections('icon')
            ->nonQueued();
    }
}
