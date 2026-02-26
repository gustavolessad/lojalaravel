<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->decimal('length', 8, 2)->nullable()->after('weight')->comment('cm');
            $table->decimal('width', 8, 2)->nullable()->after('length')->comment('cm');
            $table->decimal('height', 8, 2)->nullable()->after('width')->comment('cm');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'length', 'width', 'height']);
        });
    }
};
