<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 255);
            $table->longText('content')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();
        });

        // Páginas fixas pré-criadas
        $now = now();
        DB::table('store_pages')->insert([
            ['slug' => 'quem-somos',                      'title' => 'Quem Somos',                         'content' => null, 'meta_title' => null, 'meta_description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'politica-de-entrega',             'title' => 'Política de Entrega',                'content' => null, 'meta_title' => null, 'meta_description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'politica-de-pagamento',           'title' => 'Política de Pagamento',              'content' => null, 'meta_title' => null, 'meta_description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'politica-de-privacidade',         'title' => 'Política de Privacidade',            'content' => null, 'meta_title' => null, 'meta_description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'politica-de-trocas-e-devolucoes', 'title' => 'Política de Trocas e Devoluções',   'content' => null, 'meta_title' => null, 'meta_description' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('store_pages');
    }
};
