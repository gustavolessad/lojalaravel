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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_start')->nullable();
            $table->timestamp('sale_end')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('weight', 8, 3)->nullable()->comment('kg');
            $table->decimal('length', 8, 2)->nullable()->comment('cm');
            $table->decimal('width', 8, 2)->nullable()->comment('cm');
            $table->decimal('height', 8, 2)->nullable()->comment('cm');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
