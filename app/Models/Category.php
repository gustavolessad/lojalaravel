<?php

namespace App\Models;

use Datlechin\FilamentMenuBuilder\Concerns\HasMenuPanel;
use Datlechin\FilamentMenuBuilder\Contracts\MenuPanelable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia, MenuPanelable
{
    use HasMenuPanel, InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'order',
        'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'show_on_home' => 'boolean',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(85)
            ->fit(Fit::Contain, 400, 400)
            ->performOnCollections('icon')
            ->nonQueued();
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

    // MenuPanelable interface

    public function getMenuPanelName(): string
    {
        return 'Categorias';
    }

    public function getMenuPanelTitleColumn(): string
    {
        return 'menu_label';
    }

    public function getMenuLabelAttribute(): string
    {
        $depth = $this->breadcrumb->count() - 1;

        if ($depth === 0) {
            return $this->name;
        }

        return str_repeat('— ', $depth) . $this->name;
    }

    public function getMenuPanelUrlUsing(): callable
    {
        return fn (self $model) => $model->url;
    }

    public function getMenuPanelModifyQueryUsing(): callable
    {
        return fn (Builder $query) => $query
            ->with('parent.parent.parent')
            ->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL, name');
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
