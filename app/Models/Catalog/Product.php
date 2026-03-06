<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'brand_id',
        'type',
        'name',
        'slug',
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
        'seo_keywords',
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

        // Limpa mídia de variantes e grupos via Eloquent (antes do cascade SQL)
        static::deleting(function (Product $product) {
            $product->variantGroups->each(fn ($g) => $g->delete());
            $product->variants->each(fn ($v) => $v->delete());
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
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
            ->withPivot('expand_in_catalog', 'variant_display')
            ->orderBy('attributes.order');
    }

    public function variantGroups(): HasMany
    {
        return $this->hasMany(ProductVariantGroup::class)->orderBy('order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('order');
    }

    public function characteristicValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_characteristic_values');
    }

    public function compraJunto(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_compre_junto', 'product_id', 'related_id');
    }

    public function recommended(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_recommended', 'product_id', 'recommended_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('cover')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Versão otimizada WebP — máximo 1200px, qualidade 85%
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(85)
            ->width(1200)
            ->performOnCollections('cover', 'gallery')
            ->nonQueued();

        // Thumbnail WebP — máximo 400×400px, qualidade 80%
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->fit(Fit::Contain, 400, 400)
            ->performOnCollections('cover', 'gallery')
            ->nonQueued();
    }
}
