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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pf', 'pj'])->default('pf');

            // Pessoa Física
            $table->string('name');
            $table->string('cpf', 14)->nullable()->unique();
            $table->date('birth_date')->nullable();

            // Pessoa Jurídica
            $table->string('company_name')->nullable();
            $table->string('cnpj', 18)->nullable()->unique();
            $table->string('state_registration')->nullable();
            $table->string('responsible_name')->nullable();

            // Contato (comuns)
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
