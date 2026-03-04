<?php

namespace App\Imports;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTag;
use App\Models\ProductVariant;
use App\Models\ProductVariantGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductCsvImporter
{
    private array $errors   = [];
    private array $warnings = [];
    private int $created    = 0;
    private int $updated    = 0;
    private int $skipped    = 0;

    // Caches para evitar N+1 em lookups repetidos
    private array $brandCache          = [];
    private array $categoryCache       = [];
    private array $tagCache            = [];
    private array $attributeCache      = [];
    private array $attributeValueCache = [];

    // Mapa SKU → Product para vincular variações ao produto pai
    private array $productsBySku = [];

    private string $imagesPath;

    public function __construct(
        private readonly bool $updateExisting = true,
        private readonly bool $dryRun = false,
    ) {
        $this->imagesPath = storage_path('app/import/images');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Entrada principal
    // ─────────────────────────────────────────────────────────────────────────

    public function import(string $csvFilePath): array
    {
        if (! file_exists($csvFilePath)) {
            return $this->buildResult(['Arquivo CSV não encontrado: ' . $csvFilePath]);
        }

        $rows = $this->parseCsv($csvFilePath);

        if (empty($rows)) {
            return $this->buildResult(['CSV vazio ou não foi possível ler o arquivo.']);
        }

        // Valida colunas mínimas obrigatórias
        $requiredCols = ['type', 'name', 'price'];
        $cols         = array_keys($rows[0]);
        foreach ($requiredCols as $col) {
            if (! in_array($col, $cols, true)) {
                $this->errors[] = "Coluna obrigatória ausente no cabeçalho: '{$col}'";
            }
        }
        if (! empty($this->errors)) {
            return $this->buildResult();
        }

        DB::beginTransaction();

        try {
            // 1ª passagem: produtos simples e variáveis
            foreach ($rows as $index => $row) {
                $type = strtolower(trim($row['type'] ?? ''));
                if (in_array($type, ['simple', 'variable'], true)) {
                    $this->processProductRow($row, $index + 2);
                }
            }

            // 2ª passagem: variações (precisam que os pais já existam)
            foreach ($rows as $index => $row) {
                $type = strtolower(trim($row['type'] ?? ''));
                if ($type === 'variation') {
                    $this->processVariationRow($row, $index + 2);
                }
            }

            $this->dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductCsvImporter: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->errors[] = 'Erro interno: ' . $e->getMessage();
        }

        return $this->buildResult();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Parsing do CSV
    // ─────────────────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $headers = [];

        // ── Detecta encoding do arquivo lendo uma amostra ──────────────────
        // Excel no Windows costuma salvar em Windows-1252 em vez de UTF-8.
        $sample   = file_get_contents($path, false, null, 0, 4096) ?: '';
        $hasBom   = str_starts_with($sample, "\xEF\xBB\xBF");
        $encoding = 'UTF-8'; // default

        if (! $hasBom) {
            // mb_detect_encoding com ordem: UTF-8 → Windows-1252 → ISO-8859-1
            $detected = mb_detect_encoding($sample, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
            if ($detected && $detected !== 'UTF-8') {
                $encoding = $detected;
            }
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        // Pula BOM se existir
        if ($hasBom) {
            fread($handle, 3);
        }

        // Detecta delimitador na primeira linha
        $firstLine = fgets($handle);
        if ($encoding !== 'UTF-8') {
            $firstLine = mb_convert_encoding($firstLine, 'UTF-8', $encoding);
        }
        rewind($handle);
        if ($hasBom) {
            fread($handle, 3);
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        // Helper: converte valor para UTF-8 quando necessário
        $toUtf8 = function (string $value) use ($encoding): string {
            if ($encoding !== 'UTF-8') {
                return mb_convert_encoding($value, 'UTF-8', $encoding);
            }
            // Mesmo que o arquivo declare UTF-8, valida e converte sequências inválidas
            if (! mb_check_encoding($value, 'UTF-8')) {
                return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }
            return $value;
        };

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($headers)) {
                $headers = array_map(
                    fn ($h) => trim(mb_strtolower($toUtf8($h))),
                    $data
                );
                continue;
            }

            // Ignora linhas completamente vazias
            if (count(array_filter($data, fn ($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $i => $header) {
                $raw             = isset($data[$i]) ? $data[$i] : '';
                $row[$header]    = trim($toUtf8($raw));
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Processamento de produtos simples / variáveis
    // ─────────────────────────────────────────────────────────────────────────

    private function processProductRow(array $row, int $line): void
    {
        $name = $row['name'] ?? '';
        $sku  = $row['sku']  ?? '';
        $type = strtolower(trim($row['type'] ?? 'simple'));

        if (empty($name)) {
            $this->errors[] = "Linha {$line}: coluna 'name' é obrigatória.";
            $this->skipped++;
            return;
        }

        if (empty($row['price'])) {
            $this->errors[] = "Linha {$line}: coluna 'price' é obrigatória para produtos.";
            $this->skipped++;
            return;
        }

        $existing = ! empty($sku) ? Product::where('sku', $sku)->first() : null;

        if ($existing && ! $this->updateExisting) {
            $this->skipped++;
            return;
        }

        $isNew   = ! $existing;
        $product = $existing ?? new Product();

        $this->mapProductFields($product, $row, $type);

        if (! $this->dryRun) {
            $product->save();

            $this->syncCategories($product, $row['categories'] ?? '');
            $this->syncTags($product, $row['tags'] ?? '');
            $this->syncCharacteristics($product, $row['characteristics'] ?? '');

            if ($type === 'variable' && ! empty($row['variant_attributes'])) {
                $this->syncVariantAttributes($product, $row['variant_attributes']);
            }

            $this->attachImages($product, $row['cover_image'] ?? '', $row['gallery_images'] ?? '', 'cover', 'gallery', $isNew);
        }

        if (! empty($sku)) {
            $this->productsBySku[$sku] = $product;
        }

        $isNew ? $this->created++ : $this->updated++;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Processamento de variações
    // ─────────────────────────────────────────────────────────────────────────

    private function processVariationRow(array $row, int $line): void
    {
        $parentSku = $row['parent_sku'] ?? '';

        if (empty($parentSku)) {
            $this->errors[] = "Linha {$line}: 'parent_sku' é obrigatório para linhas do tipo 'variation'.";
            $this->skipped++;
            return;
        }

        $product = $this->productsBySku[$parentSku]
            ?? Product::where('sku', $parentSku)->first();

        if (! $product) {
            $this->errors[] = "Linha {$line}: produto pai com SKU '{$parentSku}' não encontrado.";
            $this->skipped++;
            return;
        }

        $sku      = $row['sku'] ?? '';
        $existing = ! empty($sku) ? ProductVariant::where('sku', $sku)->first() : null;

        if ($existing && ! $this->updateExisting) {
            $this->skipped++;
            return;
        }

        $isNew   = ! $existing;
        $variant = $existing ?? new ProductVariant(['product_id' => $product->id]);

        $variant->product_id = $product->id;
        $variant->sku        = $sku ?: null;
        $variant->price      = ! empty($row['price'])      ? (float) $row['price']      : null;
        $variant->sale_price = ! empty($row['sale_price']) ? (float) $row['sale_price'] : null;
        $variant->stock      = $row['stock'] !== '' ? (int) $row['stock'] : 0;
        $variant->weight     = ! empty($row['weight']) ? (float) $row['weight'] : null;
        $variant->length     = ! empty($row['length']) ? (float) $row['length'] : null;
        $variant->width      = ! empty($row['width'])  ? (float) $row['width']  : null;
        $variant->height     = ! empty($row['height']) ? (float) $row['height'] : null;
        $variant->active     = $this->parseBool($row['active'] ?? '1');

        if (! $this->dryRun) {
            $variant->save();

            $variantValues = $row['variant_values'] ?? '';
            $group         = null;

            if (! empty($variantValues)) {
                $valueIds = $this->resolveAttributeValueIds($variantValues);
                if (! empty($valueIds)) {
                    $variant->attributeValues()->sync($valueIds);
                    $this->ensureProductHasVariantAttributes($product, $variantValues);
                    $group = $this->assignVariantGroup($variant, $product, $variantValues);
                }
            }

            // Imagens vão para o GRUPO (fonte primária de exibição).
            // Só anexa se o grupo ainda não tem cover (várias variantes do mesmo
            // grupo compartilham as mesmas imagens — basta definir na 1ª delas).
            if ($group !== null) {
                $groupIsNew = $group->getFirstMedia('group-cover') === null;
                $this->attachImages(
                    $group,
                    $row['cover_image']   ?? '',
                    $row['gallery_images'] ?? '',
                    'group-cover',
                    'group-gallery',
                    $groupIsNew,
                );
            }
        }

        $isNew ? $this->created++ : $this->updated++;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Mapeamento de campos
    // ─────────────────────────────────────────────────────────────────────────

    private function mapProductFields(Product $product, array $row, string $type): void
    {
        $product->type = $type;
        $product->name = $row['name'];

        // Slug único
        $slug         = ! empty($row['slug']) ? $row['slug'] : Str::slug($row['name']);
        $originalSlug = $slug;
        $counter      = 1;
        while (
            Product::where('slug', $slug)
                ->when($product->exists, fn ($q) => $q->where('id', '!=', $product->id))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $product->slug = $slug;

        $product->sku              = $row['sku']      ?: null;
        $product->barcode          = $row['barcode']  ?: null;
        $product->price            = (float) $row['price'];
        $product->sale_price       = ! empty($row['sale_price'])  ? (float) $row['sale_price']  : null;
        $product->sale_start       = $this->parseDate($row['sale_start'] ?? '');
        $product->sale_end         = $this->parseDate($row['sale_end']   ?? '');
        $product->stock            = $type === 'simple' ? (int) ($row['stock'] ?? 0) : 0;
        $product->weight           = ! empty($row['weight']) ? (float) $row['weight'] : null;
        $product->length           = ! empty($row['length']) ? (float) $row['length'] : null;
        $product->width            = ! empty($row['width'])  ? (float) $row['width']  : null;
        $product->height           = ! empty($row['height']) ? (float) $row['height'] : null;
        $product->description      = $row['description']       ?: null;
        $product->featured         = $this->parseBool($row['featured'] ?? '0');
        $product->is_new           = $this->parseBool($row['is_new']   ?? '0');
        $product->active           = $this->parseBool($row['active']   ?? '1');
        $product->seo_title        = $row['seo_title']       ?: null;
        $product->seo_description  = $row['seo_description'] ?: null;
        $product->seo_keywords     = $row['seo_keywords']    ?: null;

        if (! empty($row['brand'])) {
            $product->brand_id = $this->findOrCreateBrand(trim($row['brand']))->id;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sincronização de relacionamentos
    // ─────────────────────────────────────────────────────────────────────────

    private function syncCategories(Product $product, string $raw): void
    {
        if (empty($raw)) {
            return;
        }

        $ids     = [];
        $entries = array_filter(array_map('trim', explode('|', $raw)));
        foreach ($entries as $entry) {
            $cat = $this->findOrCreateCategoryByPath($entry);
            if ($cat) {
                $ids[] = $cat->id;
            }
        }

        if (! empty($ids)) {
            $product->categories()->sync($ids);
        }
    }

    private function syncTags(Product $product, string $raw): void
    {
        if (empty($raw)) {
            return;
        }

        $ids   = [];
        $names = array_filter(array_map('trim', explode('|', $raw)));
        foreach ($names as $name) {
            $ids[] = $this->findOrCreateTag($name)->id;
        }

        if (! empty($ids)) {
            $product->tags()->sync($ids);
        }
    }

    private function syncCharacteristics(Product $product, string $raw): void
    {
        if (empty($raw)) {
            return;
        }

        $ids   = [];
        $pairs = array_filter(array_map('trim', explode('|', $raw)));
        foreach ($pairs as $pair) {
            if (! str_contains($pair, ':')) {
                continue;
            }
            [$attrName, $valueName] = array_map('trim', explode(':', $pair, 2));
            $av = $this->findOrCreateAttributeValue($attrName, $valueName);
            if ($av) {
                $ids[] = $av->id;
            }
        }

        if (! empty($ids)) {
            $product->characteristicValues()->sync($ids);
        }
    }

    private function syncVariantAttributes(Product $product, string $raw): void
    {
        if (empty($raw)) {
            return;
        }

        $sync  = [];
        $names = array_filter(array_map('trim', explode('|', $raw)));
        foreach ($names as $name) {
            $attr          = $this->findOrCreateAttribute($name);
            $sync[$attr->id] = ['expand_in_catalog' => false];
        }

        if (! empty($sync)) {
            $product->attributes()->syncWithoutDetaching($sync);
        }
    }

    private function ensureProductHasVariantAttributes(Product $product, string $variantValuesRaw): void
    {
        $pairs = array_filter(array_map('trim', explode('|', $variantValuesRaw)));
        $sync  = [];
        foreach ($pairs as $pair) {
            if (! str_contains($pair, ':')) {
                continue;
            }
            [$attrName] = array_map('trim', explode(':', $pair, 2));
            $attr        = $this->findOrCreateAttribute($attrName);
            $sync[$attr->id] = ['expand_in_catalog' => false];
        }
        if (! empty($sync)) {
            $product->attributes()->syncWithoutDetaching($sync);
        }
    }

    private function resolveAttributeValueIds(string $raw): array
    {
        $ids   = [];
        $pairs = array_filter(array_map('trim', explode('|', $raw)));
        foreach ($pairs as $pair) {
            if (! str_contains($pair, ':')) {
                continue;
            }
            [$attrName, $valueName] = array_map('trim', explode(':', $pair, 2));
            $av = $this->findOrCreateAttributeValue($attrName, $valueName);
            if ($av) {
                $ids[] = $av->id;
            }
        }
        return $ids;
    }

    private function assignVariantGroup(ProductVariant $variant, Product $product, string $variantValuesRaw): ?ProductVariantGroup
    {
        // Usa o valor do 1º atributo como nome do grupo (geralmente Cor)
        $pairs = array_filter(array_map('trim', explode('|', $variantValuesRaw)));
        if (empty($pairs)) {
            return null;
        }

        $firstPair = $pairs[0];
        if (! str_contains($firstPair, ':')) {
            return null;
        }

        [, $groupName] = array_map('trim', explode(':', $firstPair, 2));
        if (empty($groupName)) {
            return null;
        }

        $group = ProductVariantGroup::firstOrCreate(
            ['product_id' => $product->id, 'name' => $groupName],
            ['order' => 0]
        );

        $variant->variant_group_id = $group->id;
        $variant->saveQuietly();

        return $group;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Imagens via Spatie Media Library
    // ─────────────────────────────────────────────────────────────────────────

    private function attachImages(
        Product|ProductVariant|ProductVariantGroup $model,
        string $coverFile,
        string $galleryFiles,
        string $coverCollection,
        string $galleryCollection,
        bool $isNew,
    ): void {
        // Cover: só adiciona se informado no CSV e (produto novo ou cover vazio)
        if (! empty($coverFile)) {
            $alreadyHasCover = ! $isNew && $model->getFirstMedia($coverCollection) !== null;

            if (! $alreadyHasCover) {
                $path = $this->resolveImagePath($coverFile);
                if ($path) {
                    try {
                        $model->addMedia($path)
                            ->preservingOriginal()
                            ->toMediaCollection($coverCollection);
                    } catch (\Throwable $e) {
                        $this->warnings[] = "Cover '{$coverFile}': " . $e->getMessage();
                    }
                } else {
                    $this->warnings[] = "Imagem não encontrada na pasta de staging: {$coverFile}";
                }
            }
        }

        // Galeria: adiciona apenas arquivos que ainda não estão na coleção
        if (! empty($galleryFiles)) {
            $existingNames = $model->getMedia($galleryCollection)->pluck('file_name')->toArray();
            $files         = array_filter(array_map('trim', explode('|', $galleryFiles)));

            foreach ($files as $file) {
                if (in_array($file, $existingNames, true)) {
                    continue; // evita duplicata
                }
                $path = $this->resolveImagePath($file);
                if ($path) {
                    try {
                        $model->addMedia($path)
                            ->preservingOriginal()
                            ->toMediaCollection($galleryCollection);
                    } catch (\Throwable $e) {
                        $this->warnings[] = "Galeria '{$file}': " . $e->getMessage();
                    }
                } else {
                    $this->warnings[] = "Imagem não encontrada na pasta de staging: {$file}";
                }
            }
        }
    }

    private function resolveImagePath(string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $relativePath = 'import/images/' . ltrim($filename, '/\\');

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($relativePath)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->path($relativePath);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Find-or-create helpers com cache
    // ─────────────────────────────────────────────────────────────────────────

    private function findOrCreateBrand(string $name): Brand
    {
        $key = mb_strtolower($name);
        if (! isset($this->brandCache[$key])) {
            $this->brandCache[$key] = Brand::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'active' => true]
            );
        }
        return $this->brandCache[$key];
    }

    /**
     * Suporta "Pai > Filho > Neto" para criar hierarquia de categorias.
     */
    private function findOrCreateCategoryByPath(string $path): ?Category
    {
        $parts = array_filter(array_map('trim', explode('>', $path)));
        if (empty($parts)) {
            return null;
        }

        $parent   = null;
        $category = null;

        foreach ($parts as $part) {
            $cacheKey = ($parent ? $parent->id . ':' : '0:') . mb_strtolower($part);
            if (! isset($this->categoryCache[$cacheKey])) {
                $query = Category::where('name', $part);
                $parent
                    ? $query->where('parent_id', $parent->id)
                    : $query->whereNull('parent_id');

                $found = $query->first() ?? Category::create([
                    'name'      => $part,
                    'slug'      => $this->uniqueCategorySlug(Str::slug($part)),
                    'parent_id' => $parent?->id,
                ]);

                $this->categoryCache[$cacheKey] = $found;
            }
            $category = $this->categoryCache[$cacheKey];
            $parent   = $category;
        }

        return $category;
    }

    private function uniqueCategorySlug(string $slug): string
    {
        $original = $slug;
        $counter  = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }
        return $slug;
    }

    private function findOrCreateTag(string $name): ProductTag
    {
        $key = mb_strtolower($name);
        if (! isset($this->tagCache[$key])) {
            $this->tagCache[$key] = ProductTag::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
        return $this->tagCache[$key];
    }

    private function findOrCreateAttribute(string $name): Attribute
    {
        $key = mb_strtolower($name);
        if (! isset($this->attributeCache[$key])) {
            $this->attributeCache[$key] = Attribute::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'order' => 0]
            );
        }
        return $this->attributeCache[$key];
    }

    private function findOrCreateAttributeValue(string $attrName, string $valueName): ?AttributeValue
    {
        if (empty($attrName) || empty($valueName)) {
            return null;
        }

        $attribute = $this->findOrCreateAttribute($attrName);
        $key       = $attribute->id . ':' . mb_strtolower($valueName);

        if (! isset($this->attributeValueCache[$key])) {
            $this->attributeValueCache[$key] = AttributeValue::firstOrCreate(
                ['attribute_id' => $attribute->id, 'value' => $valueName],
                ['slug' => Str::slug($valueName), 'order' => 0]
            );
        }
        return $this->attributeValueCache[$key];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Utilitários
    // ─────────────────────────────────────────────────────────────────────────

    private function parseDate(string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBool(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['1', 'true', 'yes', 'sim', 's', 'y'], true);
    }

    private function buildResult(array $extra = []): array
    {
        return [
            'created'  => $this->created,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'errors'   => array_merge($this->errors, $extra),
            'warnings' => $this->warnings,
            'dry_run'  => $this->dryRun,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Template CSV
    // ─────────────────────────────────────────────────────────────────────────

    public static function generateTemplate(): string
    {
        $headers = [
            'type', 'name', 'slug', 'sku', 'barcode',
            'price', 'sale_price', 'sale_start', 'sale_end',
            'stock', 'weight', 'length', 'width', 'height',
            'brand', 'categories', 'tags',
            'description',
            'featured', 'is_new', 'active',
            'seo_title', 'seo_description', 'seo_keywords',
            'cover_image', 'gallery_images',
            'variant_attributes', 'characteristics',
            'parent_sku', 'variant_values',
        ];

        /*
         * GUIA RÁPIDO
         * ──────────────────────────────────────────────────────────────────
         * type         : simple | variable | variation
         * categories   : separadas por | e hierarquia com >  ex: Roupas > Masculino|Camisetas
         * tags         : separadas por |   ex: novo|destaque
         * characteristics : Atributo:Valor separados por |  ex: Material:Algodão|País:Brasil
         *                   (atributos descritivos, não geram variantes)
         * variant_attributes : nomes dos atributos de variação separados por |  ex: Cor|Tamanho
         *                      (preencher apenas na linha variable)
         * variant_values : Atributo:Valor separados por |   ex: Cor:Preto|Tamanho:38
         *                  (preencher apenas nas linhas variation)
         * cover_image  : nome do arquivo na pasta de staging  ex: camiseta-branca.jpg
         * gallery_images: nomes separados por |               ex: foto1.jpg|foto2.jpg
         *
         * IMAGENS EM PRODUTOS VARIÁVEIS
         * ──────────────────────────────────────────────────────────────────
         * - Linha variable  → cover_image/gallery_images vão para o PRODUTO PAI
         * - Linha variation → cover_image/gallery_images vão para o GRUPO DE IMAGENS
         *   O grupo é criado automaticamente usando o valor do 1º atributo (ex: "Preto").
         *   Variantes com o mesmo 1º valor (ex: Preto/P e Preto/M) compartilham o mesmo grupo.
         *   Por isso, coloque as imagens apenas na PRIMEIRA variante de cada cor/grupo.
         *   As demais variantes do mesmo grupo podem deixar cover_image em branco.
         *
         * CAMPOS OBRIGATÓRIOS
         * ──────────────────────────────────────────────────────────────────
         * simple/variable : name, price
         * variation       : parent_sku, variant_values
         *
         * sale_start/sale_end: formato YYYY-MM-DD HH:MM:SS ou DD/MM/YYYY
         * featured/is_new/active: 1 = sim, 0 = não  (active padrão = 1)
         */
        $examples = [

            // ── PRODUTO SIMPLES ──────────────────────────────────────────────
            // 1 linha = 1 produto completo. cover_image vai para product.cover
            [
                'simple',                       // type
                'Camiseta Básica Branca',        // name
                '',                             // slug (gerado automaticamente se vazio)
                'CAM-BR-001',                   // sku
                '7891234567890',                // barcode (EAN/código de barras, opcional)
                '49.90',                        // price
                '39.90',                        // sale_price (deixe vazio se não houver)
                '',                             // sale_start (ex: 2025-12-01 00:00:00)
                '',                             // sale_end
                '10',                           // stock
                '0.300',                        // weight (kg)
                '30',                           // length (cm)
                '25',                           // width (cm)
                '2',                            // height (cm)
                'Nike',                         // brand (criada automaticamente se não existir)
                'Roupas > Masculino|Camisetas', // categories (| separa múltiplas; > cria hierarquia)
                'novo|destaque',                // tags
                'Descrição completa do produto.',     // description (aceita HTML)
                '0',                            // featured (1=sim)
                '1',                            // is_new (1=sim)
                '1',                            // active (1=sim)
                '',                             // seo_title
                '',                             // seo_description
                '',                             // seo_keywords
                'camiseta-branca.jpg',          // cover_image → product.cover
                'camiseta-branca-2.jpg|camiseta-branca-3.jpg', // gallery_images → product.gallery
                '',                             // variant_attributes (vazio para simple)
                'Material:Algodão|País:Brasil', // characteristics (descritivos, não geram variantes)
                '',                             // parent_sku (vazio para simple/variable)
                '',                             // variant_values (vazio para simple/variable)
            ],

            // ── PRODUTO VARIÁVEL — linha do produto pai ──────────────────────
            // Preencha: name, price (base), brand, categories, description,
            // variant_attributes (quais atributos geram variantes), cover_image (opcional).
            // NÃO preencha stock aqui — o estoque fica nas variações.
            [
                'variable',                     // type
                'Tênis Runner Pro',             // name
                '',                             // slug
                'TEN-RUN-001',                  // sku do produto pai
                '',                             // barcode
                '299.90',                       // price base
                '',                             // sale_price
                '',                             // sale_start
                '',                             // sale_end
                '',                             // stock (vazio — estoque nas variações)
                '0.800',                        // weight
                '32',                           // length
                '22',                           // width
                '12',                           // height
                'Adidas',                       // brand
                'Calçados > Masculino',         // categories
                '',                             // tags
                'Descrição completa do tênis.', // description
                '0',                            // featured
                '0',                            // is_new
                '1',                            // active
                '',                             // seo_title
                '',                             // seo_description
                '',                             // seo_keywords
                'tenis-runner.jpg',             // cover_image → product.cover (foto genérica)
                '',                             // gallery_images → product.gallery
                'Cor|Tamanho',                  // variant_attributes ← OBRIGATÓRIO para variable
                'Tecnologia:Boost',             // characteristics
                '',                             // parent_sku (vazio)
                '',                             // variant_values (vazio)
            ],

            // ── VARIAÇÕES do Tênis Runner Pro ────────────────────────────────
            // Cada linha é uma combinação única de atributos.
            // variant_values: Atributo:Valor separados por | — 1º atributo define o GRUPO DE IMAGENS.
            // cover_image aqui → group-cover do grupo "Preto" (exibido ao selecionar cor Preto).
            // Coloque imagens apenas na 1ª variação de cada cor; as demais podem deixar em branco.

            // Cor:Preto — 1ª variação desta cor: coloca a imagem do grupo
            [
                'variation', '', '', 'TEN-RUN-PR-38', '',
                '',      '', '', '',         // price (herda do pai se vazio), sale_price, datas
                '5',                         // stock desta variação
                '',      '', '', '',         // weight/length/width/height (herda do pai se vazio)
                '', '', '',                  // brand, categories, tags (ignorados em variation)
                '',                          // description (ignorada em variation)
                '', '', '1',                 // featured, is_new, active
                '', '', '',                  // seo
                'tenis-preto.jpg',           // cover_image → group-cover do grupo "Preto"
                'tenis-preto-lat.jpg|tenis-preto-sol.jpg', // gallery_images → group-gallery "Preto"
                '', '',                      // variant_attributes, characteristics (ignorados)
                'TEN-RUN-001',               // parent_sku ← SKU do produto pai
                'Cor:Preto|Tamanho:38',      // variant_values ← 1º atributo = nome do grupo
            ],
            // Cor:Preto — 2ª variação: mesmo grupo, sem imagem (já definida acima)
            [
                'variation', '', '', 'TEN-RUN-PR-39', '',
                '', '', '', '',
                '3',
                '', '', '', '',
                '', '', '',
                '',
                '', '', '1',
                '', '', '',
                '',                          // cover_image VAZIO — grupo "Preto" já tem imagem
                '',                          // gallery_images VAZIO
                '', '',
                'TEN-RUN-001',
                'Cor:Preto|Tamanho:39',
            ],
            // Cor:Preto — 3ª variação: mesmo grupo, sem imagem
            [
                'variation', '', '', 'TEN-RUN-PR-40', '',
                '', '', '', '',
                '4',
                '', '', '', '',
                '', '', '',
                '',
                '', '', '1',
                '', '', '',
                '',
                '',
                '', '',
                'TEN-RUN-001',
                'Cor:Preto|Tamanho:40',
            ],
            // Cor:Branco — 1ª variação desta cor: coloca imagem do grupo "Branco"
            [
                'variation', '', '', 'TEN-RUN-BR-38', '',
                '279.90',                    // price diferente da cor Branco (opcional)
                '', '', '',
                '7',
                '', '', '', '',
                '', '', '',
                '',
                '', '', '1',
                '', '', '',
                'tenis-branco.jpg',          // cover_image → group-cover do grupo "Branco"
                'tenis-branco-lat.jpg',      // gallery_images → group-gallery "Branco"
                '', '',
                'TEN-RUN-001',
                'Cor:Branco|Tamanho:38',
            ],
            // Cor:Branco — 2ª variação: sem imagem
            [
                'variation', '', '', 'TEN-RUN-BR-39', '',
                '279.90', '', '', '',
                '5',
                '', '', '', '',
                '', '', '',
                '',
                '', '', '1',
                '', '', '',
                '',
                '',
                '', '',
                'TEN-RUN-001',
                'Cor:Branco|Tamanho:39',
            ],
        ];

        $output = fopen('php://temp', 'r+');
        // BOM UTF-8 para Excel abrir corretamente
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);
        foreach ($examples as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
