<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();

            // Cliente (null = guest)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();

            // Status
            $table->enum('status', [
                'pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded',
            ])->default('pending');

            // Pagamento
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['pix', 'credit_card', 'boleto'])->nullable();
            $table->string('payment_id')->nullable();    // ID do Asaas
            $table->json('payment_data')->nullable();   // QR code, link, etc.

            // Endereço de entrega (snapshot)
            $table->string('shipping_name');
            $table->string('shipping_phone', 20)->nullable();
            $table->string('shipping_zip', 9);
            $table->string('shipping_street');
            $table->string('shipping_number', 20);
            $table->string('shipping_complement')->nullable();
            $table->string('shipping_district');
            $table->string('shipping_city');
            $table->string('shipping_state', 2);

            // Frete
            $table->string('shipping_method')->nullable();
            $table->unsignedTinyInteger('shipping_days')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);

            // Valores
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Observações do cliente
            $table->text('notes')->nullable();

            // Timestamps de evento
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
