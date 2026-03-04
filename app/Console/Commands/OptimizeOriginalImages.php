<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OptimizeOriginalImages extends Command
{
    protected $signature = 'media:optimize-originals
                            {--max-width=2400 : Largura máxima em pixels}
                            {--quality=85 : Qualidade da compressão (1-100)}
                            {--dry-run : Apenas mostra o que seria feito}';

    protected $description = 'Converte originais para WebP, redimensiona e comprime para economizar disco';

    public function handle(): int
    {
        $maxWidth = (int) $this->option('max-width');
        $quality  = (int) $this->option('quality');
        $dryRun   = $this->option('dry-run');

        $medias = Media::where('mime_type', 'like', 'image/%')
            ->where('mime_type', '!=', 'image/svg+xml')
            ->orderBy('id')
            ->get();

        $this->info("Encontrados {$medias->count()} registros de imagem.");

        $totalSaved = 0;
        $optimized  = 0;
        $skipped    = 0;
        $errors     = 0;

        $bar = $this->output->createProgressBar($medias->count());
        $bar->start();

        foreach ($medias as $media) {
            $originalPath = $media->getPath();

            if (! file_exists($originalPath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $sizeBefore = filesize($originalPath);
            $isAlreadyWebp = $media->mime_type === 'image/webp';

            if ($dryRun) {
                $dims = @getimagesize($originalPath);
                $needsResize  = $dims && $dims[0] > $maxWidth;
                $needsConvert = ! $isAlreadyWebp;

                if ($needsResize || $needsConvert) {
                    $reason = [];
                    if ($needsConvert) {
                        $reason[] = strtoupper(pathinfo($media->file_name, PATHINFO_EXTENSION)) . ' → WebP';
                    }
                    if ($needsResize) {
                        $reason[] = "{$dims[0]}x{$dims[1]} → max {$maxWidth}px";
                    }
                    $this->newLine();
                    $this->line("  [DRY-RUN] {$media->file_name} (" . implode(', ', $reason) . ', ' . $this->formatBytes($sizeBefore) . ')');
                    $optimized++;
                }
                $bar->advance();
                continue;
            }

            try {
                $newPath = preg_replace('/\.[^.]+$/', '.webp', $originalPath);

                Image::load($originalPath)
                    ->fit(Fit::Contain, $maxWidth, $maxWidth)
                    ->format('webp')
                    ->quality($quality)
                    ->save($newPath);

                // Remove o original se foi convertido para novo arquivo
                if ($newPath !== $originalPath && file_exists($newPath)) {
                    @unlink($originalPath);
                }

                clearstatcache(true, $newPath);
                $sizeAfter = filesize($newPath);
                $saved     = $sizeBefore - $sizeAfter;

                if ($saved > 0) {
                    $totalSaved += $saved;
                }
                $optimized++;

                // Atualiza registro no banco
                $media->file_name = preg_replace('/\.[^.]+$/', '.webp', $media->file_name);
                $media->mime_type  = 'image/webp';
                $media->size       = $sizeAfter;
                $media->save();
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("  Erro em {$media->file_name}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Otimizados: {$optimized}");
        $this->info("Ignorados:  {$skipped}");

        if ($errors > 0) {
            $this->warn("Erros:      {$errors}");
        }

        if (! $dryRun && $totalSaved > 0) {
            $this->info("Espaço liberado: {$this->formatBytes($totalSaved)}");
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }

        return round($bytes / 1024, 1) . ' KB';
    }
}
