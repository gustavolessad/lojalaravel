<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();         // ex: NATAL20
            $table->string('description')->nullable();    // uso interno
            $table->enum('type', ['percent', 'fixed']);   // % ou R$
            $table->decimal('value', 10, 2);              // 20 = 20% ou R$ 20
            $table->decimal('min_cart_value', 10, 2)->nullable(); // mínimo do carrinho
            $table->unsignedInteger('max_uses')->nullable();         // limite total
            $table->unsignedInteger('max_uses_per_customer')->nullable(); // limite por cliente
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2);   // valor efetivo descontado
            $table->timestamps();

            $table->index(['coupon_id', 'customer_id']);
        });

        Schema::create('coupon_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'include_category',
                'exclude_category',
                'include_product',
                'exclude_product',
            ]);
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            $table->index(['coupon_id', 'type']);
        });

        // Cupom persistido no carrinho
        Schema::table('carts', function (Blueprint $table) {
            $table->string('coupon_code', 50)->nullable()->after('session_id');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_code');
        });

        // Referência do cupom no pedido
        Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_code', 50)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn ($t) => $t->dropColumn('coupon_code'));
        Schema::table('carts',  fn ($t) => $t->dropColumn(['coupon_code', 'coupon_discount']));
        Schema::dropIfExists('coupon_conditions');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
