<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('variant_group_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variant_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ProductVariantGroup::class, 'variant_group_id');
            $table->dropColumn('variant_group_id');
        });

        Schema::dropIfExists('product_variant_groups');
    }
};
