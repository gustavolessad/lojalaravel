<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'display_value',
        'color_hex',
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
}
