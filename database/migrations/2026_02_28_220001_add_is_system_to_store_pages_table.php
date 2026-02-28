<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_pages', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('meta_description');
        });

        // Marca as 5 páginas fixas como sistema (não podem ser deletadas)
        DB::table('store_pages')->whereIn('slug', [
            'quem-somos',
            'politica-de-entrega',
            'politica-de-pagamento',
            'politica-de-privacidade',
            'politica-de-trocas-e-devolucoes',
        ])->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('store_pages', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
