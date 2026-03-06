<?php

namespace App\Models\Content;

use Datlechin\FilamentMenuBuilder\Concerns\HasMenuPanel;
use Datlechin\FilamentMenuBuilder\Contracts\MenuPanelable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StorePage extends Model implements MenuPanelable
{
    use HasMenuPanel;
    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    // MenuPanelable interface

    public function getMenuPanelName(): string
    {
        return 'Páginas';
    }

    public function getMenuPanelTitleColumn(): string
    {
        return 'title';
    }

    public function getMenuPanelUrlUsing(): callable
    {
        return fn (self $model) => route('page.show', $model->slug);
    }

    public function getMenuPanelModifyQueryUsing(): callable
    {
        return fn (Builder $query) => $query->orderBy('title');
    }
}
