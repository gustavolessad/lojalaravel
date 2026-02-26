<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'sale_price',
        'sale_start',
        'sale_end',
        'sku',
        'barcode',
        'stock',
        'weight',
        'length',
        'width',
        'height',
        'seo_title',
        'seo_description',
        'featured',
        'is_new',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_start' => 'datetime',
            'sale_end'   => 'datetime',
            'weight'     => 'decimal:3',
            'length'     => 'decimal:2',
            'width'      => 'decimal:2',
            'height'     => 'decimal:2',
            'featured'   => 'boolean',
            'is_new'     => 'boolean',
            'active'     => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function isSimple(): bool
    {
        return $this->type === 'simple';
    }

    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    public function isOnSale(): bool
    {
        if (! $this->sale_price) {
            return false;
        }

        $now = now();

        if ($this->sale_start && $now->lt($this->sale_start)) {
            return false;
        }

        if ($this->sale_end && $now->gt($this->sale_end)) {
            return false;
        }

        return true;
    }

    public function getCurrentPrice(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag', 'product_id', 'product_tag_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('expand_in_catalog');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('cover')->singleFile();
    }
}
