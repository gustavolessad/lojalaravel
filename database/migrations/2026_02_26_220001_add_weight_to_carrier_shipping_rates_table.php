<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrier_shipping_rates', function (Blueprint $table) {
            // Peso mínimo em kg (padrão 0 = sem restrição inferior)
            $table->decimal('weight_from', 8, 2)->default(0)->after('cep_to');
            // Peso máximo em kg (null = sem limite superior)
            $table->decimal('weight_to', 8, 2)->nullable()->after('weight_from');
        });
    }

    public function down(): void
    {
        Schema::table('carrier_shipping_rates', function (Blueprint $table) {
            $table->dropColumn(['weight_from', 'weight_to']);
        });
    }
};
