<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Faixa de CEP (null = qualquer CEP)
            $table->char('cep_from', 8)->nullable();
            $table->char('cep_to', 8)->nullable();

            // Valor mínimo do carrinho (null = sem mínimo)
            $table->decimal('min_cart_value', 10, 2)->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('free_shipping_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')
                ->constrained('free_shipping_rules')
                ->cascadeOnDelete();

            // include_category = aplica só SE tiver produto dessa categoria
            // exclude_category = NÃO aplica SE tiver produto dessa categoria
            // include_product  = aplica só SE tiver esse produto
            // exclude_product  = NÃO aplica SE tiver esse produto
            $table->enum('type', [
                'include_category',
                'exclude_category',
                'include_product',
                'exclude_product',
            ]);

            $table->unsignedBigInteger('item_id'); // ID da categoria ou produto
            $table->timestamps();

            $table->index(['rule_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_shipping_rule_conditions');
        Schema::dropIfExists('free_shipping_rules');
    }
};
