<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'weight',
        'length',
        'width',
        'height',
        'active',
        'order',
        'attribute_value_ids', // virtual — dispara sync via model event
    ];

    /** IDs a sincronizar após save (não é coluna de DB). */
    public ?array $syncAttributeValueIds = null;

    protected $attributes = [
        'order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight'     => 'decimal:3',
            'length'     => 'decimal:2',
            'width'      => 'decimal:2',
            'height'     => 'decimal:2',
            'active'     => 'boolean',
        ];
    }

    /**
     * Setter virtual: armazena IDs para sync no evento saved.
     */
    public function setAttributeValueIdsAttribute(mixed $ids): void
    {
        $this->syncAttributeValueIds = array_values(array_filter((array) $ids));
    }

    protected static function booted(): void
    {
        static::saved(function (ProductVariant $variant) {
            if ($variant->syncAttributeValueIds !== null) {
                $variant->attributeValues()->sync($variant->syncAttributeValueIds);
                $variant->syncAttributeValueIds = null;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_values');
    }

    public function isOnSale(): bool
    {
        return (bool) $this->sale_price && $this->sale_price < $this->price;
    }

    public function getEffectivePrice(): float
    {
        if ($this->sale_price) {
            return (float) $this->sale_price;
        }

        return (float) ($this->price ?? $this->product->getCurrentPrice());
    }

    public function getLabelAttribute(): string
    {
        return $this->attributeValues->map(fn ($v) => $v->getLabel())->join(' / ');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('variant-gallery');
        $this->addMediaCollection('variant-cover')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->width(1200)
            ->performOnCollections('variant-cover', 'variant-gallery')
            ->nonQueued();

        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->fit(Fit::Contain, 400, 400)
            ->performOnCollections('variant-cover', 'variant-gallery')
            ->nonQueued();
    }
}
