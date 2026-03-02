<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    /**
     * Caminho completo de slugs, ex: "eletronicos/smartphones".
     */
    public function getPathAttribute(): string
    {
        $segments = [];
        $category = $this;

        while ($category) {
            array_unshift($segments, $category->slug);
            $category = $category->parent;
        }

        return implode('/', $segments);
    }

    /**
     * URL da categoria na loja, ex: "/eletronicos/smartphones/c".
     */
    public function getUrlAttribute(): string
    {
        return '/' . $this->path . '/c';
    }

    /**
     * Ancestrais ordenados do mais alto ao atual (para breadcrumb).
     */
    public function getBreadcrumbAttribute(): Collection
    {
        $trail = collect();
        $category = $this;

        while ($category) {
            $trail->prepend($category);
            $category = $category->parent;
        }

        return $trail;
    }

    /**
     * IDs desta categoria e todos os seus descendentes.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        $toProcess = [$this->id];

        while (! empty($toProcess)) {
            $children = static::whereIn('parent_id', $toProcess)->pluck('id')->toArray();
            $ids = array_merge($ids, $children);
            $toProcess = $children;
        }

        return $ids;
    }

    /**
     * Resolve uma categoria a partir do caminho de slugs, ex: "eletronicos/smartphones".
     */
    public static function findByPath(string $path): ?static
    {
        $slugs = explode('/', trim($path, '/'));
        $parentId = null;
        $category = null;

        foreach ($slugs as $slug) {
            $category = static::where('slug', $slug)
                ->where('parent_id', $parentId)
                ->first();

            if (! $category) {
                return null;
            }

            $parentId = $category->id;
        }

        return $category;
    }
}
