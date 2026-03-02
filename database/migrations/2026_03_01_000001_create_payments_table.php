<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('gateway')->default('asaas');
            $table->string('gateway_payment_id')->nullable()->index();

            $table->enum('method', ['pix', 'credit_card', 'boleto']);
            $table->enum('status', ['pending', 'confirmed', 'failed', 'refunded'])->default('pending');

            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('installments')->default(1);
            $table->decimal('installment_value', 10, 2)->nullable();

            $table->text('error_message')->nullable();
            $table->json('data')->nullable(); // QR Code PIX, invoice_url, etc.

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
