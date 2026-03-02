<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'active',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brand $brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return '/' . $this->slug . '/m';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Versão WebP otimizada para exibição normal
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->width(400)
            ->performOnCollections('logo')
            ->nonQueued();

        // Thumbnail menor para listagens e cards
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->fit(Fit::Contain, 200, 80)
            ->performOnCollections('logo')
            ->nonQueued();
    }
}
