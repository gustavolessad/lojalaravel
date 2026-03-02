<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'link',
        'open_in_new_tab',
        'group',
        'active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'active'          => 'boolean',
            'order'           => 'integer',
        ];
    }

    // ── Media ─────────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop')->singleFile();
        $this->addMediaCollection('mobile')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Desktop → WebP, máx. 1920px de largura
        $this->addMediaConversion('desktop')
            ->format('webp')
            ->quality(85)
            ->width(1920)
            ->performOnCollections('desktop')
            ->nonQueued();

        // Mobile → WebP, máx. 768px de largura
        $this->addMediaConversion('mobile')
            ->format('webp')
            ->quality(85)
            ->width(768)
            ->performOnCollections('mobile')
            ->nonQueued();
    }

    // ── Helpers de URL ─────────────────────────────────────────────────────

    /** URL da imagem desktop (conversão WebP ou original como fallback). */
    public function desktopUrl(): string
    {
        return $this->getFirstMediaUrl('desktop', 'desktop')
            ?: $this->getFirstMediaUrl('desktop');
    }

    /** URL da imagem mobile (conversão WebP ou original como fallback). */
    public function mobileUrl(): string
    {
        return $this->getFirstMediaUrl('mobile', 'mobile')
            ?: $this->getFirstMediaUrl('mobile');
    }
}
