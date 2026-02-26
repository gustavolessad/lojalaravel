<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('carrier_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->char('cep_from', 8);   // somente dígitos, ex: 01000000
            $table->char('cep_to', 8);     // somente dígitos, ex: 09999999
            $table->decimal('price', 8, 2);
            $table->unsignedSmallInteger('days');
            $table->timestamps();

            $table->index(['carrier_id', 'cep_from', 'cep_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_shipping_rates');
        Schema::dropIfExists('carriers');
    }
};
