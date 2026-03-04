<?php

namespace App\Listeners;

use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class OptimizeOriginalImage
{
    private const MAX_WIDTH = 2400;

    private const QUALITY = 85;

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        // Só processa imagens raster
        if (! str_starts_with($media->mime_type, 'image/') || $media->mime_type === 'image/svg+xml') {
            return;
        }

        $originalPath = $media->getPath();

        if (! file_exists($originalPath)) {
            return;
        }

        // Novo caminho com extensão .webp
        $newPath = preg_replace('/\.[^.]+$/', '.webp', $originalPath);

        Image::load($originalPath)
            ->fit(Fit::Contain, self::MAX_WIDTH, self::MAX_WIDTH)
            ->format('webp')
            ->quality(self::QUALITY)
            ->save($newPath);

        // Remove o original se o caminho mudou
        if ($newPath !== $originalPath && file_exists($newPath)) {
            @unlink($originalPath);
        }

        // Atualiza o registro no banco
        $newFilename = preg_replace('/\.[^.]+$/', '.webp', $media->file_name);
        $media->file_name = $newFilename;
        $media->mime_type  = 'image/webp';
        $media->size       = filesize($newPath);
        $media->save();
    }
}
